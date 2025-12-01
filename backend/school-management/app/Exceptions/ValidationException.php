<?php

namespace App\Exceptions;

class ValidationException extends DomainException
{
    public function __construct(string $message, int $code = 422,)
    {
        parent::__construct($code, $message,);
    }
}

