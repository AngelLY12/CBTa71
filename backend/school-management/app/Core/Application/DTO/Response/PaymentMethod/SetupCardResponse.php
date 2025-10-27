<?php

namespace App\Core\Application\DTO\Response\PaymentMethod;

class SetupCardResponse
{
    public function __construct(
        public readonly ?string $id,
        public readonly ?string $url
    )
    {

    }
}
