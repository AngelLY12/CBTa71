<?php

namespace App\Core\Application\DTO\Response\Payment;

use Carbon\Carbon;

class PaymentListItemResponse{

     public function __construct(
        public readonly ?string $date,
        public readonly ?string $concept,
        public readonly ?string $amount,
        public readonly ?string $method,
        public readonly ?string $fullName

    ) {}
}
