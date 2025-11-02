<?php

namespace App\Exceptions\Test;

use App\Exceptions\DomainException;

class TestDomainException extends DomainException
{
    public function __construct()
    {
        parent::__construct(418, 'Soy una excepción de dominio de prueba 🫖');
    }
}
