<?php

namespace App\Core\Domain\Repositories\Command\Auth;

interface AccessTokenRepInterface
{
    public function revokeToken(string $tokenId): bool;
    public function deletionInvalidTokens(): int;
}
