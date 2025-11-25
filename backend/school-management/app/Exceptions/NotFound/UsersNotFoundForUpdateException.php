<?php

namespace App\Exceptions\NotFound;

use App\Exceptions\DomainException;

class UsersNotFoundForUpdateException extends DomainException
{
    public function __construct()
    {
        parent::__construct(404, "No se encontraron usuarios que coincidan con los criterios proporcionados.");
    }
}
