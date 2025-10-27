<?php

namespace App\Core\Application\DTO\Response\General;

use App\Core\Application\DTO\Response\PaymentConcept\PendingSummaryResponse;

class DashboardDataResponse{

    public function __construct(
        public readonly ?int $earnings,
        public readonly ?PendingSummaryResponse $pending,
        public readonly ?int $students
    )
    {

    }
}
