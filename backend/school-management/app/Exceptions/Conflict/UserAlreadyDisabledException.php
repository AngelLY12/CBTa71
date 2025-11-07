<?php

namespace App\Exceptions\Conflict;

use App\Exceptions\DomainException;

class UserAlreadyDisabledException extends DomainException
{
    public function __construct()
    {
        parent::__construct(409,'El usuario ya fue dado de baja');
    }
}
