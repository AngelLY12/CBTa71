<?php

namespace App\Exceptions\NotAllowed;

use App\Exceptions\DomainException;

class AdminRoleNotAllowedException extends DomainException
{
    public function __construct()
    {
        parent::__construct(403,"El role de admin no esta permitido.");
    }
}
