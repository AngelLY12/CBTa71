<?php

namespace App\Exceptions\NotFound;

use App\Exceptions\DomainException;

class ParentChildrenNotFoundException extends DomainException
{
    public function __construct()
    {
        parent::__construct(404, 'No se encontraron hijos relacionados a este usuario.');
    }
}
