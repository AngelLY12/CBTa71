<?php

namespace App\Exceptions\Conflict;

use App\Exceptions\DomainException;

class UserCannotBeUpdatedException extends DomainException
{
    public function __construct()
    {
        parent::__construct(409, 'No se puede actualizar un usuario que no esté activo.');
    }
}
