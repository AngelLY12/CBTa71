<?php

namespace App\Exceptions;

use Exception;

class PaymentMethodNotSupportedException extends Exception
{
    public function __construct($message = "El método de pago no es soportado.", $code = 400)
    {
        parent::__construct($message, $code);
    }
}
