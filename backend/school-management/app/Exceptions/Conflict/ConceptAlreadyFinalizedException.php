<?php

namespace App\Exceptions\Conflict;

use App\Exceptions\DomainException;

class ConceptAlreadyFinalizedException extends DomainException
{
    public function __construct()
    {
        parent::__construct(409, 'El concepto ya está finalizado.');
    }
}
