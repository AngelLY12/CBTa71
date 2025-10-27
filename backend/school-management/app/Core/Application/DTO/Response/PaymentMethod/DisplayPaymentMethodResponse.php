<?php

namespace App\Core\Application\DTO\Response\PaymentMethod;

class DisplayPaymentMethodResponse
{
    public function __construct(
        public readonly ?int $id,
        public readonly ?string $brand,
        public readonly ?string $masked_card,
        public readonly ?string $expiration_date,
        public readonly ?string $status,
    ) {}
}


