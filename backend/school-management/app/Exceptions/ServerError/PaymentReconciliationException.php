<?php

namespace App\Exceptions\ServerError;

use App\Exceptions\DomainException;

class PaymentReconciliationException extends DomainException
{
    public function __construct(string $details)
    {
        parent::__construct(500, "Error al reconciliar el pago: $details");
    }
}
