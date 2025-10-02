<?php

namespace App\Exceptions;

use Exception;

class ConceptInactiveException extends Exception
{
    public function __construct($message = "El concepto no está activo.", $code = 400)
    {
        parent::__construct($message, $code);
    }
}
