<?php

namespace App\Exceptions\Validation;

use App\Exceptions\DomainException;

class CareerSemesterInvalidException extends DomainException
{
    public function __construct()
    {
        parent::__construct(422, 'Debe especificar al menos una carrera y un semestre.');
    }
}
