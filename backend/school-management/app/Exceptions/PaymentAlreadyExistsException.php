<?php

namespace App\Exceptions;

use Exception;

class PaymentAlreadyExistsException extends Exception
{
    public function __construct($message = "El concepto ya fue pagado por el usuario.", $code = 400)
    {
        parent::__construct($message, $code);
    }
}
