<?php

namespace App\Core\Domain\Repositories\Command\Auth;

interface AccessTokenRepInterface
{
    public function revokeToken(string $tokenId): void;
    public function deletionInvalidTokens(): int;
}
