<?php

namespace App\Exceptions\NotFound;

use App\Exceptions\DomainException;

class RoleNotFoundException extends DomainException
{
    public function __construct()
    {
        parent::__construct(404,'No se encontro el rol solicitado');
    }
}
