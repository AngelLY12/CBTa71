<?php

namespace App\Core\Application\DTO\Response\User;

class UserDataResponse{
    public function __construct(
        public readonly ?int $id,
        public readonly ?string $fullName,
        public readonly ?string $email,
        public readonly ?string $curp,
        public readonly ?string $n_control
    ) {}
}
