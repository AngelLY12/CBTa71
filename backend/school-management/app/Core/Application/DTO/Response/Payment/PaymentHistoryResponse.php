<?php

namespace App\Core\Application\DTO\Response\Payment;

use Carbon\Carbon;
use Illuminate\Support\Facades\Date;

class PaymentHistoryResponse{
    public function __construct(
        public readonly ?int $id,
        public readonly ?string $concept,
        public readonly ?string $amount,
        public readonly ?string $date
    ) {}
}
