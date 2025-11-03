<?php

namespace App\Core\Application\DTO\Response\General;

use App\Core\Application\DTO\Response\PaymentConcept\PendingSummaryResponse;

class DashboardDataUserResponse
{
     public function __construct(
        public readonly ?string $completed,
        public readonly ?PendingSummaryResponse $pending,
        public readonly ?int $overdue
    )
    {

    }
}
