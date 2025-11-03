<?php

namespace App\Core\Application\DTO\Response\Payment;

use Carbon\Carbon;

class PaymentDetailResponse{
     public function __construct(
        public readonly ?int $id,
        public readonly ?string $concept,
        public readonly ?string $amount,
        public readonly ?string $date,
        public readonly ?string $status,
        public readonly ?string $reference,
        public readonly ?string $url,
        public readonly ?array $payment_method_details
    ) {}
}
