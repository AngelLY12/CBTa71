<?php

namespace App\Exceptions;

class StripeGatewayException extends DomainException
{
    public function __construct(string $message, int $code = 500,)
    {
        parent::__construct($code, $message);
    }
}
