<?php

namespace App\Exceptions;

use Exception;

class ConceptExpiredException extends Exception
{
    public function __construct($message = "El concepto no está vigente para pago.", $code = 400)
    {
        parent::__construct($message, $code);
    }
}
