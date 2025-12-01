<?php

namespace App\Exceptions\Conflict;

use App\Exceptions\DomainException;

class UserConflictStatusException extends DomainException
{
    public function __construct(string $message)
    {
        parent::__construct(409, $message);
    }
}
