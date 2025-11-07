<?php

namespace App\Exceptions\Conflict;

use App\Exceptions\DomainException;

class UserAlreadyActiveException extends DomainException
{
    public function __construct()
    {
        parent::__construct(409,'El usuario ya esta activo');
    }
}
