<?php

namespace App\Exceptions\Validation;

use App\Exceptions\DomainException;

class ConceptNotStartedException extends DomainException
{
    public function __construct()
    {
        parent::__construct(422, 'El concepto no ha iniciado, no puede ser finalizado.');
    }
}
