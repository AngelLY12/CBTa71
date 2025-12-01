<?php

namespace App\Exceptions\ServerError;

use App\Exceptions\DomainException;

class StripeCheckoutSessionException extends DomainException
{
    public function __construct()
    {
        parent::__construct(500, "Ocurrió un error al crear la sesión de pago en Stripe.");
    }
}
