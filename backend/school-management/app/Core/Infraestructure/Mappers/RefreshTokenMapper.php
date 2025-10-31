<?php

namespace App\Core\Infraestructure\Mappers;

use App\Core\Domain\Entities\RefreshToken as DomainRefreshToken;
use App\Core\Domain\Entities\User;
use App\Models\RefreshToken;

class RefreshTokenMapper
{
    public static function toDomain(RefreshToken $token): DomainRefreshToken
    {
        return new DomainRefreshToken(
            id:$token->id,
            user_id: $token->user_id,
            token:$token->token,
            expiresAt: $token->expires_at,
            revoked: $token->revoked
        );
    }

    public static function toPersistence(User $user, string $token, int $days): array
    {
        return [
            'user_id' => $user->id,
            'token' => $token,
            'expires_at' => now()->addDays($days),
            'revoked' => false,
        ];
    }
}
