<?php

namespace App\Exceptions\NotAllowed;

use App\Exceptions\DomainException;

class UserInactiveException extends DomainException
{
    public function __construct()
    {
        parent::__construct(403,"Usuario inactivo, no tienes permitido realizar esta acción.");
    }
}
