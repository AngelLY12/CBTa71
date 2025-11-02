<?php

namespace App\Exceptions\Validation;

use App\Exceptions\DomainException;

class ConceptInvalidStatusException extends DomainException
{
    public function __construct()
    {
        parent::__construct(422, 'Estado no válido.');
    }
}
