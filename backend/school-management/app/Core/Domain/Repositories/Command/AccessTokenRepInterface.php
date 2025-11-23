<?php

namespace App\Core\Domain\Repositories\Command;

interface AccessTokenRepInterface
{
    public function revokeToken(string $tokenId): void;
    public function deletionInvalidTokens(): int;
}
