<?php

namespace App\Core\Application\UseCases\Payments\Staff\Concepts;

use App\Core\Application\DTO\Request\PaymentConcept\UpdatePaymentConceptDTO;
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
use App\Jobs\ProcessPaymentConceptRecipientsJob;
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
        $this->preValidateUpdate($dto);
        $paymentConcept= DB::transaction(function() use ($dto) {
            if (isset($dto->appliesTo)) {
                $dto->fieldsToUpdate['is_global'] = $dto->appliesTo === PaymentConceptAppliesTo::TODOS;
            } else if (isset($dto->fieldsToUpdate['is_global']) && $dto->fieldsToUpdate['is_global'] === true) {
                $dto->appliesTo = PaymentConceptAppliesTo::TODOS;
            }
            $dto->fieldsToUpdate['applies_to'] = $dto->appliesTo;

            $existingConcept = $this->pcqRepo->findById($dto->id);

            if (!$existingConcept) {
                throw new ConceptNotFoundException();
            }
            PaymentConceptValidator::ensureConceptIsValidToUpdate($existingConcept);
            $paymentConcept = $this->pcRepo->update($existingConcept->id, $dto->fieldsToUpdate);
            PaymentConceptValidator::ensureConceptHasRequiredFields($paymentConcept);
            if ($dto->exceptionStudents) {
                $userIdListDTO = $this->getUserIdListDTO($dto, true);
                $paymentConcept = $this->pcRepo->attachToExceptionStudents(
                    $paymentConcept->id,
                    $userIdListDTO,
                    $dto->replaceExceptions
                );
            }
            if($dto->removeAllExceptions)
            {
                $this->pcRepo->detachFromExceptionStudents($paymentConcept->id);
            }
            if($dto->appliesTo){
                $paymentConcept=$this->attachAppliesTo($dto,$paymentConcept);
            }

            $finalAppliesTo = $paymentConcept->applies_to->value;
            $hasRecipients = $this->uqRepo->hasAnyRecipient($paymentConcept, $finalAppliesTo);

            if (!$hasRecipients) {
                throw new RecipientsNotFoundException();
            }

            return $paymentConcept;
        });
         $finalAppliesTo = $paymentConcept->applies_to->value;
         ProcessPaymentConceptRecipientsJob::forConcept(
             $paymentConcept->id,
             $finalAppliesTo
         )->delay(now()->addSeconds(rand(1, 10)));

         return $paymentConcept;
    }

    private function preValidateUpdate(UpdatePaymentConceptDTO $dto): void
    {
        PaymentConceptValidator::ensureUpdatePaymentConceptDTOIsValid($dto);
    }

    private function attachAppliesTo(UpdatePaymentConceptDTO $dto,PaymentConcept $paymentConcept): PaymentConcept
    {
        $detachFlags = $this->determineDetachFlags($paymentConcept->applies_to);

            switch($paymentConcept->applies_to) {
                case PaymentConceptAppliesTo::CARRERA:
                    if ($dto->careers) {
                        $paymentConcept=$this->pcRepo->attachToCareer($paymentConcept->id, $dto->careers,$dto->replaceRelations);
                    } else {
                        throw new CareersNotFoundException();
                    }
                    break;
                case PaymentConceptAppliesTo::SEMESTRE:

                    if ($dto->semesters) {
                        $paymentConcept=$this->pcRepo->attachToSemester($paymentConcept->id, $dto->semesters, $dto->replaceRelations);
                    }else{
                        throw new SemestersNotFoundException();
                    }
                    break;
                case PaymentConceptAppliesTo::ESTUDIANTES:
                    if ($dto->students) {
                        $userIdListDTO = $this->getUserIdListDTO($dto);
                        $paymentConcept=$this->pcRepo->attachToUsers($paymentConcept->id, $userIdListDTO, $dto->replaceRelations);

                    }else{
                        throw new StudentsNotFoundException();
                    }
                    break;
                case PaymentConceptAppliesTo::CARRERA_SEMESTRE:
                    if($dto->careers && $dto->semesters){
                        $paymentConcept = $this->pcRepo->attachToCareer($paymentConcept->id, $dto->careers, $dto->replaceRelations);
                        $paymentConcept = $this->pcRepo->attachToSemester($paymentConcept->id, $dto->semesters, $dto->replaceRelations);
                    }else {
                        throw new CareerSemesterInvalidException();
                    }
                    break;
                case PaymentConceptAppliesTo::TODOS:
                    $paymentConcept->is_global = true;
                    break;
                case PaymentConceptAppliesTo::TAG:
                    if($dto->applicantTags)
                    {
                        $paymentConcept = $this->pcRepo->attachToApplicantTag($paymentConcept->id, $dto->applicantTags ,$dto->replaceRelations);
                    }else
                    {
                        throw new ApplicantTagInvalidException();
                    }
            }
        $this->applyDetachments($paymentConcept->id, $detachFlags);

        return $paymentConcept;

    }

    private function determineDetachFlags(PaymentConceptAppliesTo $appliesTo): array
    {
        return match($appliesTo) {
            PaymentConceptAppliesTo::CARRERA => [
                'career' => false,
                'semester' => true,
                'users' => true,
                'tags' => true
            ],
            PaymentConceptAppliesTo::SEMESTRE => [
                'career' => true,
                'semester' => false,
                'users' => true,
                'tags' => true
            ],
            PaymentConceptAppliesTo::ESTUDIANTES => [
                'career' => true,
                'semester' => true,
                'users' => false,
                'tags' => true
            ],
            PaymentConceptAppliesTo::CARRERA_SEMESTRE => [
                'career' => false,
                'semester' => false,
                'users' => true,
                'tags' => true
            ],
            PaymentConceptAppliesTo::TAG => [
                'career' => true,
                'semester' => true,
                'users' => true,
                'tags' => false
            ],
            PaymentConceptAppliesTo::TODOS => [
                'career' => true,
                'semester' => true,
                'users' => true,
                'tags' => true
            ]
        };
    }

    private function applyDetachments(int $conceptId, array $detachFlags): void
    {
        if ($detachFlags['career'] ?? false) {
            $this->pcRepo->detachFromCareer($conceptId);
        }
        if ($detachFlags['semester'] ?? false) {
            $this->pcRepo->detachFromSemester($conceptId);
        }
        if ($detachFlags['users'] ?? false) {
            $this->pcRepo->detachFromUsers($conceptId);
        }
        if ($detachFlags['tags'] ?? false) {
            $this->pcRepo->detachFromApplicantTag($conceptId);
        }
    }

}
