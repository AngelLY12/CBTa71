<?php

namespace App\Core\Application\DTO\Response\User;

class UserRecipientDTO
{
    public function __construct(
        public readonly ?int $id,
        public readonly ?string $fullName,
        public readonly ?string $email,
    ) {}
}
