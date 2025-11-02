<?php

namespace App\Exceptions;

class UserInactiveException extends DomainException
{
    public function __construct()
    {
        parent::__construct(403, 'El usuario está dado de baja.');
    }
}
