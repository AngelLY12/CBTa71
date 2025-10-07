<?php

namespace App\Utils\Validators;
use App\Models\PaymentConcept;
use Carbon\Carbon;
use InvalidArgumentException;
use App\Models\Payment;
use App\Models\User;
use App\Exceptions\ConceptExpiredException;
use App\Exceptions\PaymentAlreadyExistsException;
use App\Exceptions\ConceptInactiveException;
use App\Exceptions\UserNotAllowedException;


class PaymentConceptValidator{

    public static function ensureConceptIsActiveAndValid(User $user,PaymentConcept $concept)
    {
        $today = Carbon::today();

        $existingPayment = Payment::where('user_id', $user->id)
            ->where('payment_concept_id', $concept->id)
            ->exists();

        if ($existingPayment) {
            throw new PaymentAlreadyExistsException();
        }

        if ($concept->status !== 'activo') {
            throw new ConceptInactiveException();
        }

        if ($concept->start_date > $today || ($concept->end_date !== null && $concept->end_date < $today)) {
            throw new ConceptExpiredException();
        }

        $allowed = $concept->is_global
            || $concept->users->contains($user->id)
            || $concept->careers->contains($user->career_id)
            || $concept->paymentConceptSemesters->contains('semestre', $user->semestre)
            || $user->status==='activo';

        if (!$allowed) {
            throw new UserNotAllowedException();
        }
    }

    public static function ensureConceptHasRequiredFields(PaymentConcept $concept){
        if(empty($concept->concept_name) || empty($concept->amount) || $concept->amount<0 ){
            throw new InvalidArgumentException('El concepto debe tener un nombre y monto valido');

        }
    }

}
