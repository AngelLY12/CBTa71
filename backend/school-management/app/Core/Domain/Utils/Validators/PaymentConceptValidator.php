<?php

namespace App\Core\Domain\Utils\Validators;
use App\Core\Domain\Enum\PaymentConcept\PaymentConceptApplicantType;
use Carbon\Carbon;
use App\Core\Domain\Entities\PaymentConcept;
use App\Core\Domain\Entities\User;
use App\Core\Domain\Enum\PaymentConcept\PaymentConceptStatus;
use App\Exceptions\Conflict\ConceptAlreadyActiveException;
use App\Exceptions\Conflict\ConceptAlreadyDeletedException;
use App\Exceptions\Conflict\ConceptAlreadyDisabledException;
use App\Exceptions\Conflict\ConceptAlreadyFinalizedException;
use App\Exceptions\Conflict\ConceptCannotBeDisabledException;
use App\Exceptions\Conflict\ConceptCannotBeFinalizedException;
use App\Exceptions\Conflict\ConceptCannotBeUpdatedException;
use App\Exceptions\Conflict\ConceptConflictStatusException;
use App\Exceptions\NotAllowed\UserNotAllowedException;
use App\Exceptions\Validation\ConceptEndDateBeforeStartException;
use App\Exceptions\Validation\ConceptEndDateBeforeTodayException;
use App\Exceptions\Validation\ConceptEndDateTooFarException;
use App\Exceptions\Validation\ConceptExpiredException;
use App\Exceptions\Validation\ConceptInactiveException;
use App\Exceptions\Validation\ConceptInvalidAmountException;
use App\Exceptions\Validation\ConceptInvalidEndDateException;
use App\Exceptions\Validation\ConceptInvalidStartDateException;
use App\Exceptions\Validation\ConceptMissingNameException;
use App\Exceptions\Validation\ConceptNotStartedException;
use App\Exceptions\Validation\ConceptStartDateTooEarlyException;
use App\Exceptions\Validation\ConceptStartDateTooFarException;
use App\Core\Application\DTO\Request\PaymentConcept\CreatePaymentConceptDTO;
use App\Exceptions\Conflict\ConceptAppliesToConflictException;
use App\Exceptions\Conflict\StudentsAndExceptionsOverlapException;

class PaymentConceptValidator{

    public static function ensureConceptIsActiveAndValid(PaymentConcept $concept, User $user)
    {
        if (!$concept->isActive()) {
            throw new ConceptInactiveException();
        }

        if (!$concept->hasStarted() || $concept->isExpired()) {
            throw new ConceptExpiredException();
        }

        $student = $user->studentDetail;

        $allowed =
            $concept->is_global
            || ($student && in_array($student->career_id, $concept->getCareerIds()))
            || ($student && in_array($student->semestre, $concept->getSemesters()))
            || in_array($user->id, $concept->getUserIds())
            || ($user->isApplicant() && $concept->hasTag(PaymentConceptApplicantType::APPLICANT))
            || ($user->isNewStudent() && $concept->hasTag(PaymentConceptApplicantType::NO_STUDENT_DETAILS))
            || $user->isActive();

        if (!$allowed) {
            throw new UserNotAllowedException();
        }
    }

    public static function ensureConceptHasStarted(PaymentConcept $concept)
    {
        if (!$concept->hasStarted()) {
            throw new ConceptNotStartedException();
        }
    }

    public static function ensureValidStatusTransition(PaymentConcept $concept, PaymentConceptStatus $newStatus)
    {

        $current = $concept->status;
        if ($current === $newStatus) {
           throw match ($newStatus) {
                PaymentConceptStatus::ACTIVO       => new ConceptAlreadyActiveException(),
                PaymentConceptStatus::FINALIZADO   => new ConceptAlreadyFinalizedException(),
                PaymentConceptStatus::DESACTIVADO  => new ConceptAlreadyDisabledException(),
                PaymentConceptStatus::ELIMINADO    => new ConceptAlreadyDeletedException(),
            };
        }

        if (!$current->canTransitionTo($newStatus)) {
            throw match (true) {
                $current === PaymentConceptStatus::FINALIZADO
                    && $newStatus === PaymentConceptStatus::DESACTIVADO
                    => new ConceptCannotBeDisabledException(),
                $current === PaymentConceptStatus::ELIMINADO
                    && $newStatus === PaymentConceptStatus::FINALIZADO
                    => new ConceptCannotBeFinalizedException(),
                default => new ConceptConflictStatusException(
                    "No se puede cambiar el estado de {$current->value} a {$newStatus->value}."
                ),
            };
        }
    }

    public static function ensureConceptIsValidToUpdate(PaymentConcept $concept){
        if (!$concept->status->isUpdatable()) {
            throw new ConceptCannotBeUpdatedException();
        }
    }

    public static function ensureCreatePaymentDTOIsValid(CreatePaymentConceptDTO $dto)
    {
        if ($dto->is_global && (!empty($dto->careers) || !empty($dto->semesters) || !empty($dto->students))) {
            throw new ConceptAppliesToConflictException();
        }
        $intersection = array_intersect(
            (array)$dto->students,
            (array)$dto->exceptionStudents
        );

        if (!empty($intersection)) {
            throw new StudentsAndExceptionsOverlapException();
        }
    }

    public static function ensureConceptHasRequiredFields(PaymentConcept $concept)
    {
        if (empty($concept->concept_name)) {
            throw new ConceptMissingNameException();
        }

        if ($concept->amount === null || $concept->amount < 10) {
            throw new ConceptInvalidAmountException();
        }

        if (!$concept->start_date instanceof \Carbon\Carbon) {
            throw new ConceptInvalidStartDateException();
        }

        $today = today();
        $oneMonthBefore = $today->clone()->subMonth();
        $oneMonthAfter  = $today->clone()->addMonth();

        if ($concept->start_date->gt($oneMonthAfter)) {
            throw new ConceptStartDateTooFarException();
        }

        if ($concept->start_date->lt($oneMonthBefore)) {
            throw new ConceptStartDateTooEarlyException();
        }

        if ($concept->end_date === null) {
            return;
        }

        if (!$concept->end_date instanceof \Carbon\Carbon) {
            throw new ConceptInvalidEndDateException();
        }

        if ($concept->end_date->lt($concept->start_date)) {
            throw new ConceptEndDateBeforeStartException();
        }

        if ($concept->end_date->lt($today)) {
            throw new ConceptEndDateBeforeTodayException();
        }

        if ($concept->end_date->gt($concept->start_date->clone()->addYears(5))) {
            throw new ConceptEndDateTooFarException();
        }
    }
}
