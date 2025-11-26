<?php

namespace App\Exceptions\NotAllowed;

use App\Exceptions\DomainException;

class UserInvalidRoleException extends DomainException
{
    public function __construct()
    {
        parent::__construct(403,"El usuario no tiene el rol necesario.");
    }
}
