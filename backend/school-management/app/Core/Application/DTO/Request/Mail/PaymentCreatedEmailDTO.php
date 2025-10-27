<?php

namespace App\Core\Application\DTO\Request\Mail;
use Carbon\Carbon;

class PaymentCreatedEmailDTO{
    public function __construct(
        public readonly string $recipientName,
        public readonly string $recipientEmail,
        public readonly string $concept_name,
        public readonly int $amount,
        public readonly string $created_at,
        public readonly ?string $url,
        public readonly?string $stripe_session_id
    ) {}
}
