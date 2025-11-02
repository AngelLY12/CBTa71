<?php

namespace App\Exceptions\NotFound;

use App\Exceptions\DomainException;

class RecipientsNotFoundException extends DomainException
{
    public function __construct()
    {
        parent::__construct(404, 'No se encontraron destinatarios válidos para el concepto de pago.');
    }
}
