<?php

namespace App\Exceptions\NotFound;

use App\Exceptions\DomainException;

class PermissionsByUserNotFoundException extends DomainException
{
    public function __construct()
    {
        parent::__construct(404,'No se encontraron permisos aplicables para los usuarios seleccionados');
    }
}
