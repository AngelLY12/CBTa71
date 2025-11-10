<?php

namespace App\Core\Domain\Utils\Validators;
use Carbon\Carbon;
use InvalidArgumentException;
use App\Core\Domain\Entities\PaymentConcept;
use App\Core\Domain\Entities\User;
use App\Exceptions\Conflict\ConceptAlreadyActiveException;
use App\Exceptions\Conflict\ConceptAlreadyDeletedException;
use App\Exceptions\Conflict\ConceptAlreadyDisabledException;
use App\Exceptions\Conflict\ConceptAlreadyFinalizedException;
use App\Exceptions\Conflict\ConceptCannotBeDisabledException;
use App\Exceptions\Conflict\ConceptCannotBeFinalizedException;
use App\Exceptions\Conflict\ConceptCannotBeUpdatedException;
use App\Exceptions\NotAllowed\UserNotAllowedException;
use App\Exceptions\Validation\ConceptEndDateBeforeStartException;
use App\Exceptions\Validation\ConceptEndDateBeforeTodayException;
use App\Exceptions\Validation\ConceptEndDateTooFarException;
use App\Exceptions\Validation\ConceptExpiredException;
use App\Exceptions\Validation\ConceptInactiveException;
use App\Exceptions\Validation\ConceptInvalidAmountException;
use App\Exceptions\Validation\ConceptInvalidEndDateException;
use App\Exceptions\Validation\ConceptInvalidStartDateException;
use App\Exceptions\Validation\ConceptInvalidStatusException;
use App\Exceptions\Validation\ConceptMissingNameException;
use App\Exceptions\Validation\ConceptNotStartedException;
use App\Exceptions\Validation\ConceptStartDateTooEarlyException;
use App\Exceptions\Validation\ConceptStartDateTooFarException;
use App\Exceptions\ValidationException;

class PaymentConceptValidator{

        public static function ensureConceptIsActiveAndValid(PaymentConcept $concept, User $user)
    {
        if (!$concept->isActive()) {
            throw new ConceptInactiveException();
        }

        if (!$concept->hasStarted() || $concept->isExpired()) {
            throw new ConceptExpiredException();
        }

        $allowed = $concept->is_global
            || ($user->studentDetail && in_array($user->studentDetail->career_id, $concept->getCareerIds()))
            || ($user->studentDetail && in_array($user->studentDetail->semestre, $concept->getSemesters()))
            || in_array($user->id, $concept->getUserIds())
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

    public static function ensureValidStatus(string $status)
    {
        $arrayStatus = ['todos','activos','finalizados','desactivados','eliminados'];
        if(!in_array($status,$arrayStatus,true)){
            throw new ValidationException("Estado inválido: {$status}");
        }
    }

    public static function ensureValidStatusTransition(PaymentConcept $concept, string $newStatus)
    {

        $current = $concept->status;

        switch ($current) {
            case 'activo':
                if (!in_array($newStatus, ['finalizado', 'eliminado', 'desactivado'], true)) {
                    throw new ConceptInvalidStatusException("Un concepto activo solo puede finalizarse, eliminarse o desactivarse.");
                }
                break;

            case 'finalizado':
                if (!in_array($newStatus, ['activo', 'eliminado'], true)) {
                    throw new ConceptInvalidStatusException("Un concepto finalizado solo puede reactivarse o eliminarse.");
                }
                break;

            case 'eliminado':
                if ($newStatus !== 'activo') {
                    throw new ConceptInvalidStatusException("Un concepto eliminado solo puede reactivarse.");
                }
                break;

            case 'desactivado':
                if (!in_array($newStatus, ['activo', 'eliminado'], true)) {
                    throw new ConceptInvalidStatusException("Un concepto desactivado solo puede reactivarse o eliminarse.");
                }
                break;

            default:
                throw new ConceptInvalidStatusException("Estado actual inválido: {$current}");
        }

        if ($current === $newStatus) {
            throw new ValidationException("El concepto ya está en el estado '{$newStatus}'.");
        }
    }

    public static function ensureConceptIsValidToUpdate(PaymentConcept $concept){
        if(!in_array($concept->status,['activo','desactivado'])){
            throw new ConceptCannotBeUpdatedException();
        }
    }

    public static function ensureConceptHasRequiredFields(PaymentConcept $concept)
    {
        if (empty($concept->concept_name)) {
            throw new ConceptMissingNameException();
        }

        if (is_null($concept->amount) || $concept->amount < 10) {
            throw new ConceptInvalidAmountException();
        }
        if (!$concept->start_date instanceof \Carbon\Carbon) {
            throw new ConceptInvalidStartDateException();
        }
        $today = Carbon::today();
        $oneMonthBefore = $today->copy()->subMonth();
        $oneMonthAfter = $today->copy()->addMonth();

        if ($concept->start_date->gt($oneMonthAfter)) {
            throw new ConceptStartDateTooFarException();
        }

        if ($concept->start_date->lt($oneMonthBefore)) {
            throw new ConceptStartDateTooEarlyException();
        }


        if ($concept->end_date !== null) {
            if (!$concept->end_date instanceof \Carbon\Carbon) {
                throw new ConceptInvalidEndDateException();
            }

            if ($concept->end_date->lt($concept->start_date)) {
                throw new ConceptEndDateBeforeStartException();
            }

            if ($concept->end_date->lt(\Carbon\Carbon::today())) {
                throw new ConceptEndDateBeforeTodayException();
            }

            if ($concept->end_date->gt($concept->start_date->copy()->addYears(5))) {
                throw new ConceptEndDateTooFarException();
            }
        }
    }
}
