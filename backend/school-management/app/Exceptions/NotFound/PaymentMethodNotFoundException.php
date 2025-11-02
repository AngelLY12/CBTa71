<?php

namespace App\Exceptions\NotFound;

use App\Exceptions\DomainException;

class PaymentMethodNotFoundException extends DomainException
{
    public function __construct()
    {
        parent::__construct(404, 'Método de pago no encontrado.');
    }
}
