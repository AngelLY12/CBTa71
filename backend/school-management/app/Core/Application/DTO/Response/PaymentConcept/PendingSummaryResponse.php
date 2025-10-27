<?php
namespace App\Core\Application\DTO\Response\PaymentConcept;

class PendingSummaryResponse {
    public function __construct(
        public readonly ?int $totalAmount,
        public readonly ?int $totalCount
    ) {}
}

