<?php

namespace App\Core\Application\DTO\Response\User;

class UserWithPendingSumamaryResponse{
    public function __construct(
        public readonly ?int $userId,
        public readonly ?string $fullName,
        public readonly ?int $semestre,
        public readonly ?string $career_name,
        public readonly ?int $num_pending,
        public readonly ?string $total_amount_pending
    )
    {
    }
}
