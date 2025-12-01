<?php

namespace App\Exceptions\NotFound;

use App\Exceptions\DomainException;

class StudentDetailNotFoundException extends DomainException
{
    public function __construct()
    {
        parent::__construct(404,'No se encontraron los detalles de estudiante solicitados');
    }
}
