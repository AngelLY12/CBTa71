<?php

namespace App\Exceptions;

class InvalidCredentialsException extends DomainException
{
    public function __construct()
    {
        parent::__construct(401, 'Las credenciales son incorrectas.');
    }
}
