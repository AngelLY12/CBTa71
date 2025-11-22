<?php

namespace App\Exceptions\NotAllowed;

use App\Exceptions\DomainException;

class InvalidCurrentPasswordException extends DomainException
{
    public function __construct()
    {
        parent::__construct(403,"La contraseña que proporcionaste es incorrecta.");
    }
}
