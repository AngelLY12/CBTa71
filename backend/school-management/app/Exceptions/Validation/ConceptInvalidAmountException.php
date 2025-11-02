<?php

namespace App\Exceptions\Validation;

use App\Exceptions\DomainException;

class ConceptInvalidAmountException extends DomainException
{
    public function __construct()
    {
        parent::__construct(422, 'El monto del concepto debe ser mayor a 10.');
    }
}
