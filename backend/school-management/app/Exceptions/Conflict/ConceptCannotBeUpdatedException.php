<?php

namespace App\Exceptions\Conflict;

use App\Exceptions\DomainException;

class ConceptCannotBeUpdatedException extends DomainException
{
    public function __construct()
    {
        parent::__construct(409, 'No se puede actualizar un concepto que no esté activo o desactivado.');
    }
}
