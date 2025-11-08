<?php

namespace App\Core\Domain\Repositories\Command;

use App\Core\Domain\Entities\RefreshToken;
use App\Core\Domain\Entities\User;

interface RefreshTokenRepInterface
{
    public function findByToken(string $token): ?RefreshToken;
    public function revokeRefreshToken(string $refreshTokenValue): void;
    public function create(int $userId, string $token, int $days = 7): RefreshToken;
    public function update(int $tokenId,  array $fields): RefreshToken;
    public function delete(int $tokenId): void;
    public function deletionInvalidTokens(): int;
}
