<?php

namespace App\Exceptions\NotAllowed;

use App\Exceptions\DomainException;

class UserNotAllowedException extends DomainException
{
    public function __construct()
    {
        parent::__construct(403,"El usuario no tiene permitido pagar este concepto.");
    }
}
