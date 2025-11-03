<?php

namespace App\Core\Application\DTO\Response\PaymentConcept;

use Carbon\Carbon;

class PendingPaymentConceptsResponse {
    public function __construct(
        public readonly ?int $id,
        public readonly ?string $concept_name,
        public readonly ?string $description,
        public readonly ?string $amount,
        public readonly ?string $start_date,
        public readonly ?string $end_date
    ) {}
}

