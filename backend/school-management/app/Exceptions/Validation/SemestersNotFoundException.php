<?php

namespace App\Exceptions\Validation;

use App\Exceptions\DomainException;

class SemestersNotFoundException extends DomainException
{
    public function __construct()
    {
        parent::__construct(422, 'No se especificaron semestres.');
    }
}
