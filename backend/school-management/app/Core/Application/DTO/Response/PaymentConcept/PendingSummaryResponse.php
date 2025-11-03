<?php
namespace App\Core\Application\DTO\Response\PaymentConcept;

class PendingSummaryResponse {
    public function __construct(
        public readonly ?string $totalAmount,
        public readonly ?int $totalCount
    ) {}
}

