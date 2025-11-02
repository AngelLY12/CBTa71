<?php

namespace App\Exceptions\Validation;

use App\Exceptions\DomainException;

class ConceptMissingNameException extends DomainException
{
    public function __construct()
    {
        parent::__construct(422, 'El concepto debe tener un nombre válido.');
    }
}
