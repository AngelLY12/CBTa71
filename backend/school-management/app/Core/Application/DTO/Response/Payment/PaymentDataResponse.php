<?php

namespace App\Core\Application\DTO\Response\Payment;

class PaymentDataResponse{
     public function __construct(
        public readonly ?int $id,
        public readonly ?int $amount,
        public readonly ?string $status,
        public readonly ?string $payment_intent_id,
    ) {}
}
