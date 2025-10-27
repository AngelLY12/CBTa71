<?php

namespace App\Core\Application\DTO\Response\User;

class UserWithPaymentResponse{
    public function __construct(
        public readonly ?int $id,
        public readonly ?string $fullName,
        public readonly ?string $concept,
        public readonly ?int $amount
    ) {}
}
