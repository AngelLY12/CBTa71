<?php

namespace App\Exceptions\NotFound;

use App\Exceptions\DomainException;

class ConceptNotFoundException extends DomainException
{
    public function __construct()
    {
        parent::__construct(404, 'El concepto solicitado no fue encontrado.');
    }
}
