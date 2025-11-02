<?php

namespace App\Exceptions\Validation;

use App\Exceptions\DomainException;

class ConceptInactiveException extends DomainException
{
    public function __construct()
    {
        parent::__construct(422, 'El concepto no está activo.');
    }
}
