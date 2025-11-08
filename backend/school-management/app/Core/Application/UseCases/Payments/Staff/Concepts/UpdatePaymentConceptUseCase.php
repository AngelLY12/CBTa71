<?php

namespace App\Core\Application\UseCases\Payments\Staff\Concepts;

use App\Core\Application\DTO\Request\PaymentConcept\UpdatePaymentConceptDTO;
use App\Core\Application\Mappers\MailMapper;
use App\Core\Domain\Entities\PaymentConcept;
use App\Core\Domain\Repositories\Command\Payments\PaymentConceptRepInterface;
use App\Core\Domain\Repositories\Query\Payments\PaymentConceptQueryRepInterface;
use App\Core\Domain\Repositories\Query\UserQueryRepInterface;
use App\Core\Domain\Utils\Validators\PaymentConceptValidator;
use App\Exceptions\Conflict\ConceptAppliesToConflictException;
use App\Exceptions\NotFound\CareersNotFoundException;
use App\Exceptions\NotFound\ConceptNotFoundException;
use App\Exceptions\NotFound\RecipientsNotFoundException;
use App\Exceptions\NotFound\StudentsNotFoundException;
use App\Exceptions\Validation\CareerSemesterInvalidException;
use App\Exceptions\Validation\SemestersNotFoundException;
use App\Jobs\SendMailJob;
use App\Mail\NewConceptMail;
use Illuminate\Support\Facades\DB;

class UpdatePaymentConceptUseCase
{
    public function __construct(
        private PaymentConceptRepInterface $pcRepo,
        private PaymentConceptQueryRepInterface $pcqRepo,
        private UserQueryRepInterface $uqRepo,
    )
    {}
     public function execute(UpdatePaymentConceptDTO $dto): PaymentConcept {
        return DB::transaction(function() use ($dto) {
            if (isset($dto->appliesTo)) {
                $dto->fieldsToUpdate['is_global'] = $dto->appliesTo === 'todos';
            } else if (isset($dto->fieldsToUpdate['is_global']) && $dto->fieldsToUpdate['is_global'] === true) {
                $dto->appliesTo = 'todos';
            }
            if (($dto->fieldsToUpdate['is_global'] ?? false) && (!empty($dto->careers) || !empty($dto->semesters) || !empty($dto->students))) {
                throw new ConceptAppliesToConflictException();
            }

            $existingConcept = $this->pcqRepo->findById($dto->id);

            if (!$existingConcept) {
                throw new ConceptNotFoundException();
            }
            PaymentConceptValidator::ensureConceptIsValidToUpdate($existingConcept);
            $paymentConcept = $this->pcRepo->update($existingConcept->id, $dto->fieldsToUpdate);
            PaymentConceptValidator::ensureConceptHasRequiredFields($paymentConcept);
            $detachCareer = $detachSemester = $detachUsers = false;

            if ($dto->appliesTo) {
                switch($dto->appliesTo) {
                    case 'carrera':
                        $detachSemester = true;
                        $detachUsers = true;
                        if ($dto->careers) {
                            $paymentConcept=$this->pcRepo->attachToCareer($paymentConcept->id, $dto->careers,$dto->replaceRelations);
                        } else {
                             throw new CareersNotFoundException();
                        }
                        break;
                    case 'semestre':
                        $detachCareer = true;
                        $detachUsers = true;
                        if ($dto->semesters) {
                            $paymentConcept=$this->pcRepo->attachToSemester($paymentConcept->id, $dto->semesters);
                        }else{
                            throw new SemestersNotFoundException();
                        }
                        break;
                    case 'estudiantes':
                        $detachCareer = true;
                        $detachSemester = true;
                        if ($dto->students) {
                            $userIdListDTO = $this->uqRepo->getUserIdsByControlNumbers((array)$dto->students);
                            if (empty($userIdListDTO->userIds)) {
                                throw new StudentsNotFoundException();

                            }
                            $paymentConcept=$this->pcRepo->attachToUsers($paymentConcept->id, $userIdListDTO, $dto->replaceRelations);

                        }else{
                            throw new StudentsNotFoundException();
                        }
                        break;
                    case 'carrera_semestre':
                        $detachUsers = true;
                        if($dto->careers && $dto->semesters){
                            $paymentConcept = $this->pcRepo->attachToCareer($paymentConcept->id, $dto->careers);
                            $paymentConcept = $this->pcRepo->attachToSemester($paymentConcept->id, $dto->semesters);
                        }else {
                            throw new CareerSemesterInvalidException();
                        }
                        break;
                    case 'todos':
                        $detachCareer = $detachSemester = $detachUsers = true;
                        $paymentConcept->is_global = true;
                        break;
                    default:
                        $detachCareer = $detachSemester = $detachUsers = true;
                        break;
                }
            }
            if ($detachCareer) $this->pcRepo->detachFromCareer($paymentConcept->id);
            if ($detachSemester) $this->pcRepo->detachFromSemester($paymentConcept->id);
            if ($detachUsers) $this->pcRepo->detachFromUsers($paymentConcept->id);

            $recipients = $this->uqRepo->getRecipients($paymentConcept, $dto->appliesTo ?? 'todos');
            if(empty($recipientsArray)){
                throw new RecipientsNotFoundException();
            }
            $this->notifyRecipients($paymentConcept,$recipients);
            return $paymentConcept;
        });
    }
    private function notifyRecipients(PaymentConcept $concept, array $recipients): void {
        foreach($recipients as $user) {
            $data = [
                'recipientName'=>$user->name,
                'recipientEmail' => $user->email,
                'concept_name' => $concept->concept_name,
                'amount' => $concept->amount,
                'end_date' => $concept->end_date
            ];
            SendMailJob::dispatch(new NewConceptMail(MailMapper::toNewPaymentConceptEmailDTO($data)))->delay(now()->addSeconds(rand(1, 5)));
        }
    }
}
