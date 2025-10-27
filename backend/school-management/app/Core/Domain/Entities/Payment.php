<?php

namespace App\Core\Domain\Entities;

class Payment
{
    public function __construct(
        public ?int $id = null,
        public int $user_id,
        public ?int $payment_concept_id,
        public ?int $payment_method_id=null,
        public ?string $stripe_payment_method_id=null,
        public ?string $concept_name,
        public ?int $amount,
        public ?array $payment_method_details = [],
        public string $status,
        public ?string $payment_intent_id=null,
        public ?string $url,
        public ?string $stripe_session_id
    ) {}


}
