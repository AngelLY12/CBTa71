<?php

namespace App\Exceptions\NotAllowed;

use App\Exceptions\DomainException;

class PaymentRetryNotAllowedException extends DomainException
{
    public function __construct()
    {
        parent::__construct(403, "No se permite repetir el pago de este concepto.");
    }

}
