<?php

namespace App\Exceptions\NotFound;

use App\Exceptions\DomainException;

class UserNotFoundException extends DomainException
{
    public function __construct()
    {
        parent::__construct(404, "Usuario no encontrado");
    }
}
