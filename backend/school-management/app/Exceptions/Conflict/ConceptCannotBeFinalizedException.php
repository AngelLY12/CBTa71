<?php

namespace App\Exceptions\Conflict;

use App\Exceptions\DomainException;

class ConceptCannotBeFinalizedException extends DomainException
{
    public function __construct()
    {
        parent::__construct(409, 'No se puede finalizar un concepto eliminado.');
    }
}
