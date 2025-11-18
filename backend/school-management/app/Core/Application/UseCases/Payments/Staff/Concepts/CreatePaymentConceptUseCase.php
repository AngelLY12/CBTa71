<?php

namespace App\Core\Application\UseCases\Payments\Staff\Concepts;

use App\Core\Application\DTO\Request\PaymentConcept\CreatePaymentConceptDTO;
use App\Core\Application\Mappers\MailMapper;
use App\Core\Domain\Entities\PaymentConcept;
use App\Core\Domain\Repositories\Command\Payments\PaymentConceptRepInterface;
use App\Core\Domain\Repositories\Query\Payments\PaymentConceptQueryRepInterface;
use App\Core\Domain\Repositories\Query\UserQueryRepInterface;
use App\Core\Domain\Utils\Validators\PaymentConceptValidator;
use App\Exceptions\Conflict\ConceptAppliesToConflictException;
use App\Exceptions\NotFound\CareersNotFoundException;
use App\Exceptions\NotFound\RecipientsNotFoundException;
use App\Exceptions\NotFound\StudentsNotFoundException;
use App\Exceptions\Validation\CareerSemesterInvalidException;
use App\Exceptions\Validation\SemestersNotFoundException;
use App\Jobs\ClearStudentConceptCacheJob;
use App\Jobs\SendMailJob;
use App\Mail\NewConceptMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreatePaymentConceptUseCase
{
    public function __construct(
        private PaymentConceptRepInterface $pcRepo,
        private PaymentConceptQueryRepInterface $pcqRepo,
        private UserQueryRepInterface $uqRepo,
    )
    {}

    public function execute(CreatePaymentConceptDTO $dto): PaymentConcept {
        return DB::transaction(function() use ($dto) {
            $dto->is_global = $dto->appliesTo === 'todos';
            if ($dto->is_global && (!empty($dto->careers) || !empty($dto->semesters) || !empty($dto->students))) {
                throw new ConceptAppliesToConflictException();
            }

                $pc = new PaymentConcept(
                concept_name: $dto->concept_name,
                description: $dto->description,
                amount: $dto->amount,
                status: $dto->status,
                start_date: $dto->start_date,
                end_date: $dto->end_date,
                applies_to:$dto->appliesTo,
                is_global: $dto->is_global,
                id:null
            );
            PaymentConceptValidator::ensureConceptHasRequiredFields($pc);

            $paymentConcept = $this->pcRepo->create($pc);

            switch($dto->appliesTo) {
                case 'carrera':
                    if ($dto->careers) {
                        $paymentConcept=$this->pcRepo->attachToCareer($paymentConcept->id, $dto->careers);

                    } else {
                        throw new CareersNotFoundException();
                    }
                    break;
                case 'semestre':
                    if ($dto->semesters) {
                        $paymentConcept=$this->pcRepo->attachToSemester($paymentConcept->id, $dto->semesters);
                    } else {
                        throw new SemestersNotFoundException();
                    }
                    break;
                case 'estudiantes':
                    if ($dto->students) {
                        $userIdListDTO = $this->uqRepo->getUserIdsByControlNumbers((array)$dto->students);
                        if (empty($userIdListDTO->userIds)) {
                            throw new StudentsNotFoundException();
                        }
                        $paymentConcept=$this->pcRepo->attachToUsers($paymentConcept->id, $userIdListDTO);
                    }else{
                        throw new StudentsNotFoundException();
                    }
                    break;
                case 'carrera_semestre':
                    if($dto->careers && $dto->semesters){
                        $paymentConcept = $this->pcRepo->attachToCareer($paymentConcept->id, $dto->careers);
                        $paymentConcept = $this->pcRepo->attachToSemester($paymentConcept->id, $dto->semesters);
                    }else {
                        throw new CareerSemesterInvalidException();
                    }
                    break;
                case 'todos':
                    break;
                default:
                    break;
            }
            $recipients = $this->uqRepo->getRecipients($paymentConcept, $dto->appliesTo);
            if(empty($recipientsArray)){
                throw new RecipientsNotFoundException();
            }
            $this->notifyRecipients($paymentConcept,$recipients);

            return $paymentConcept;
        });
    }
    private function notifyRecipients(PaymentConcept $concept, array $recipients): void {
        foreach($recipients as $user) {
            ClearStudentConceptCacheJob::dispatch($user->id)->delay(now()->addSeconds(rand(1, 10)));
            $data = [
                'recipientName'=>$user->name,
                'recipientEmail' => $user->email,
                'concept_name' => $concept->concept_name,
                'amount' => $concept->amount,
                'end_date' => $concept->end_date
            ];
            SendMailJob::dispatch(new NewConceptMail(MailMapper::toNewPaymentConceptEmailDTO($data)), $user->email)->delay(now()->addSeconds(rand(1, 5)));
        }
    }
}
