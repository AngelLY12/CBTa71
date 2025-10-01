<?php

namespace App\Utils\Validators;
use App\Models\PaymentConcept;
use App\Utils\ResponseBuilder;
use Carbon\Carbon;
use InvalidArgumentException;

class PaymentConceptValidator{

    public static function ensureConceptIsActiveAndValid(PaymentConcept $concept)
    {
        $today = Carbon::today();

        if ($concept->status !== 'Activo') {
            throw new InvalidArgumentException('El concepto no está activo');
        }

        if ($concept->start_date > $today || $concept->end_date < $today) {
            throw new InvalidArgumentException('El concepto no está vigente');
        }
    }

    public static function ensureConceptHasRequiredFields(PaymentConcept $concept){
        if(empty($concept->concept_name) || empty($concept->amount) || $concept->amount<0 ){
            throw new InvalidArgumentException('El concepto debe tener un nombre y monto valido');

        }
    }

}
