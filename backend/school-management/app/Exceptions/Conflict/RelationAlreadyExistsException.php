<?php

namespace App\Exceptions\Conflict;

use App\Exceptions\DomainException;

class RelationAlreadyExistsException extends DomainException
{
    public function __construct()
    {
        parent::__construct(409,"Un pariente con este email ya fue enlazado al estudiante.");
    }
}
