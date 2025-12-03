<?php

namespace App\Exceptions\Conflict;

use App\Exceptions\DomainException;

class StudentsAndExceptionsOverlapException extends DomainException
{
    public function __construct()
    {
        parent::__construct(409,'Hay un conflicto entre los estudiantes a los que aplica el concepto y los excentos al mismo.');
    }
}
