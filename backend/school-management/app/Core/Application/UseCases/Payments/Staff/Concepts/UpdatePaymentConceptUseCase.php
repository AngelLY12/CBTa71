<?php

namespace App\Core\Application\UseCases\Payments\Staff\Concepts;

use App\Core\Application\DTO\Request\PaymentConcept\UpdatePaymentConceptDTO;
use App\Core\Application\Mappers\MailMapper;
use App\Core\Application\Traits\HasPaymentConcept;
use App\Core\Domain\Entities\PaymentConcept;
use App\Core\Domain\Enum\PaymentConcept\PaymentConceptAppliesTo;
use App\Core\Domain\Repositories\Command\Payments\PaymentConceptRepInterface;
use App\Core\Domain\Repositories\Query\Payments\PaymentConceptQueryRepInterface;
use App\Core\Domain\Repositories\Query\User\UserQueryRepInterface;
use App\Core\Domain\Utils\Validators\PaymentConceptValidator;
use App\Exceptions\Conflict\ConceptAppliesToConflictException;
use App\Exceptions\NotFound\CareersNotFoundException;
use App\Exceptions\NotFound\ConceptNotFoundException;
use App\Exceptions\NotFound\RecipientsNotFoundException;
use App\Exceptions\NotFound\StudentsNotFoundException;
use App\Exceptions\Validation\ApplicantTagInvalidException;
use App\Exceptions\Validation\CareerSemesterInvalidException;
use App\Exceptions\Validation\SemestersNotFoundException;
use App\Jobs\ClearCacheWhileStatusChangeJob;
use App\Jobs\SendMailJob;
use App\Mail\NewConceptMail;
use Illuminate\Support\Facades\DB;

class UpdatePaymentConceptUseCase
{
    use HasPaymentConcept;
    public function __construct(
        private PaymentConceptRepInterface $pcRepo,
        private PaymentConceptQueryRepInterface $pcqRepo,
        private UserQueryRepInterface $uqRepo,
    )
    {
        $this->setRepository($uqRepo);
    }
     public function execute(UpdatePaymentConceptDTO $dto): PaymentConcept {
        return DB::transaction(function() use ($dto) {
            if (isset($dto->appliesTo)) {
                $dto->fieldsToUpdate['is_global'] = $dto->appliesTo === PaymentConceptAppliesTo::TODOS;
            } else if (isset($dto->fieldsToUpdate['is_global']) && $dto->fieldsToUpdate['is_global'] === true) {
                $dto->appliesTo = PaymentConceptAppliesTo::TODOS;
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

            $paymentConcept=$this->attachAppliesTo($dto, $paymentConcept);

            if ($dto->exceptionStudents) {
                $userIdListDTO = $this->getUserIdListDTO($dto, true);

                $paymentConcept = $this->pcRepo->attachToExceptionStudents(
                    $paymentConcept->id,
                    $userIdListDTO,
                    $dto->replaceExceptions
                );
            }
            $recipients = $this->uqRepo->getRecipients($paymentConcept, $dto->appliesTo ?? 'todos');
            if(empty($recipients)){
                throw new RecipientsNotFoundException();
            }
            $this->notifyRecipients($paymentConcept,$recipients);
            return $paymentConcept;
        });
    }

    private function attachAppliesTo(UpdatePaymentConceptDTO $dto, PaymentConcept $paymentConcept): PaymentConcept
    {
        $detachCareer = $detachSemester = $detachUsers = $detachTags = false;

        if ($dto->appliesTo) {
            switch($dto->appliesTo) {
                case PaymentConceptAppliesTo::CARRERA:
                    $detachSemester = true;
                    $detachUsers = true;
                    $detachTags = true;
                    if ($dto->careers) {
                        $paymentConcept=$this->pcRepo->attachToCareer($paymentConcept->id, $dto->careers,$dto->replaceRelations);
                    } else {
                        throw new CareersNotFoundException();
                    }
                    break;
                case PaymentConceptAppliesTo::SEMESTRE:
                    $detachCareer = true;
                    $detachUsers = true;
                    $detachTags = true;
                    if ($dto->semesters) {
                        $paymentConcept=$this->pcRepo->attachToSemester($paymentConcept->id, $dto->semesters, $dto->replaceRelations);
                    }else{
                        throw new SemestersNotFoundException();
                    }
                    break;
                case PaymentConceptAppliesTo::ESTUDIANTES:
                    $detachCareer = true;
                    $detachSemester = true;
                    $detachTags= true;
                    if ($dto->students) {
                        $userIdListDTO = $this->getUserIdListDTO($dto);
                        $paymentConcept=$this->pcRepo->attachToUsers($paymentConcept->id, $userIdListDTO, $dto->replaceRelations);

                    }else{
                        throw new StudentsNotFoundException();
                    }
                    break;
                case PaymentConceptAppliesTo::CARRERA_SEMESTRE:
                    $detachUsers = true;
                    $detachTags = true;
                    if($dto->careers && $dto->semesters){
                        $paymentConcept = $this->pcRepo->attachToCareer($paymentConcept->id, $dto->careers);
                        $paymentConcept = $this->pcRepo->attachToSemester($paymentConcept->id, $dto->semesters);
                    }else {
                        throw new CareerSemesterInvalidException();
                    }
                    break;
                case PaymentConceptAppliesTo::TODOS:
                    $detachCareer = $detachSemester = $detachUsers = true;
                    $paymentConcept->is_global = true;
                    break;
                case PaymentConceptAppliesTo::TAG:
                    $detachCareer = $detachSemester = $detachUsers = true;
                    if($dto->applicantTags)
                    {
                        $paymentConcept = $this->pcRepo->attachToApplicantTag($paymentConcept->id, $dto->applicantTags ,$dto->replaceRelations);
                    }else
                    {
                        throw new ApplicantTagInvalidException();
                    }
                default:
                    $detachCareer = $detachSemester = $detachUsers = true;
                    break;
            }
        }
        if ($detachCareer) $this->pcRepo->detachFromCareer($paymentConcept->id);
        if ($detachSemester) $this->pcRepo->detachFromSemester($paymentConcept->id);
        if ($detachUsers) $this->pcRepo->detachFromUsers($paymentConcept->id);
        if($detachTags) $this->pcRepo->detachFromApplicantTag($paymentConcept->id);

        return $paymentConcept;

    }


}
