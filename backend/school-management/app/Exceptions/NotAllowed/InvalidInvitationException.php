<?php

namespace App\Exceptions\NotAllowed;

use App\Exceptions\DomainException;

class InvalidInvitationException extends DomainException
{
    public function __construct()
    {
        parent::__construct(403,"La invitación no es valida (Ya ha sido usada o expiro).");
    }
}
