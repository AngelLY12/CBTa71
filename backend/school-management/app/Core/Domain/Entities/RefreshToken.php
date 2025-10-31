<?php

namespace App\Core\Domain\Entities;
use Carbon\CarbonImmutable;

class RefreshToken
{
    public function __construct(
        public readonly int $id,
        public readonly int $user_id,
        public readonly string $token,
        public readonly CarbonImmutable $expiresAt,
        public bool $revoked = false
    ) {}

    public function isExpired(): bool
    {
        return $this->expiresAt->isPast();
    }

    public function isValid(): bool
    {
        return !$this->revoked && !$this->isExpired();
    }

    public function revoke(): void
    {
        $this->revoked = true;
    }
}
