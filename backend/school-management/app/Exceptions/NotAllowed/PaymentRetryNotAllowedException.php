<?php

namespace App\Exceptions\NotAllowed;

use App\Exceptions\DomainException;

class PaymentRetryNotAllowedException extends DomainException
{
    public function __construct($message)
    {
        parent::__construct(403, $message);
    }

}
