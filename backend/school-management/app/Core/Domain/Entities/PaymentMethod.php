<?php

namespace App\Core\Domain\Entities;

use DateTime;

class PaymentMethod{

    public function __construct(
        public int $user_id,
        public string $stripe_payment_method_id,
        public ?string $brand = null,
        public ?string $last4 = null,
        public ?string $exp_month = null,
        public ?string $exp_year = null,
        public ?int $id = null,
    )
    {}

   public function isExpired(): bool
    {
        if (!$this->exp_month || !$this->exp_year) {
            return false;
        }

        $expiration = DateTime::createFromFormat('Y-n', "{$this->exp_year}-{$this->exp_month}");
        if (!$expiration) {
            return false;
        }

        $expiration->modify('last day of this month 23:59:59');
        $now = new DateTime();

        return $now > $expiration;
    }

   public function expirationDate(): string
    {
        if (!$this->exp_month || !$this->exp_year) {
            return 'N/A';
        }

        $yearShort = substr((string)$this->exp_year, -2);
        return sprintf('%02d/%s', $this->exp_month, $yearShort);
    }


    public function getMaskedCard(): ?string
    {
        return $this->last4 ? "**** **** **** {$this->last4}" : null;
    }
}
