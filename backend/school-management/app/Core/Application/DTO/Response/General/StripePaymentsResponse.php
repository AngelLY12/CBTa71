<?php

namespace App\Core\Application\DTO\Response\General;

class StripePaymentsResponse
{
    public function __construct(
        public readonly ?string $id,
        public readonly ?string $payment_intent_id,
        public readonly ?string $concept_name,
        public readonly ?string $status,
        public readonly ?int $amount_total,
        public readonly ?string $created,
    )
    {}
}
