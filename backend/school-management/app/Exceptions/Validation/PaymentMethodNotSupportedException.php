<?php

namespace App\Exceptions\Validation;

use App\Exceptions\DomainException;

class PaymentMethodNotSupportedException extends DomainException
{
    public function __construct()
    {
        parent::__construct(422,"El método de pago no es soportado.");
    }
}
