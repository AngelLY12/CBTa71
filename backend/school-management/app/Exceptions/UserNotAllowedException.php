<?php

namespace App\Exceptions;

use Exception;

class UserNotAllowedException extends Exception
{
    public function __construct($message = "El usuario no tiene permitido pagar este concepto.", $code = 403)
    {
        parent::__construct($message, $code);
    }
}
