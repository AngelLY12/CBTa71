<?php

namespace App\Exceptions\Conflict;

use App\Exceptions\DomainException;

class ConceptAlreadyDisabledException extends DomainException
{
    public function __construct()
    {
        parent::__construct(409, 'El concepto ya está desactivado.');
    }
}
