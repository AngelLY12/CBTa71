<?php

namespace App\Exceptions\NotFound;

use App\Exceptions\DomainException;

class StudentsNotFoundException extends DomainException
{
    public function __construct()
    {
        parent::__construct(404, 'Ninguno de los estudiantes existe o está dado de baja.');
    }
}
