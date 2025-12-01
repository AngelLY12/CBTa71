<?php
namespace App\Exceptions\Conflict;

use App\Exceptions\DomainException;

class UserAlreadyDeletedException extends DomainException
{
    public function __construct()
    {
        parent::__construct(409, 'El usuario ya fue eliminado');
    }
}
