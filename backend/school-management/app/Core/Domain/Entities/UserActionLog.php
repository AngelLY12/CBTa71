<?php

namespace App\Core\Domain\Entities;

class UserActionLog
{
    public function __construct(
        public ?int $userId,
        public ?array $roles,
        public ?string $ip,
        public string $method,
        public string $url,
    )
    {
    }

    public function toArray(): array
    {
        return [
            'userId' => $this->userId,
            'roles' => $this->roles,
            'ip' => $this->ip,
            'method' => $this->method,
            'url' => $this->url,
        ];
    }
}
