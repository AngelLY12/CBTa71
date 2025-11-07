<?php

namespace App\Exceptions\Conflict;

use App\Exceptions\DomainException;

class UserCannotBeDisabledException extends DomainException
{
    public function __construct()
    {
        parent::__construct(409, 'No se puede dar de baja a un usuario eliminado');
    }
}
