<?php

namespace App\Exceptions\Conflict;

use App\Exceptions\DomainException;

class UserAlreadyHaveStudenDetailException extends DomainException
{
    public function __construct()
    {
        parent::__construct(409, 'El usuario ya tiene detalles de estudiante asignados');
    }
}
