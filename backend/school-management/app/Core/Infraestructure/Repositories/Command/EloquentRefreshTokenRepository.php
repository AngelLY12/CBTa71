<?php

namespace App\Core\Infraestructure\Repositories\Command;

use App\Core\Domain\Entities\RefreshToken as EntitiesRefreshToken;
use App\Core\Domain\Entities\User;
use App\Core\Domain\Repositories\Command\RefreshToken;
use App\Core\Domain\Repositories\Command\RefreshTokenRepInterface;
use App\Core\Infraestructure\Mappers\RefreshTokenMapper;
use App\Models\RefreshToken as ModelsRefreshToken;

class EloquentRefreshTokenRepository implements RefreshTokenRepInterface
{
    public function findByToken(string $token): ?EntitiesRefreshToken
    {
        $eloquent= ModelsRefreshToken::where('token', $token)->first();
        return RefreshTokenMapper::toDomain($eloquent);
    }
    public function create(User $user, string $token, int $days = 7): EntitiesRefreshToken
    {
        $eloquent = ModelsRefreshToken::create(
            RefreshTokenMapper::toPersistence($user, $token, $days)
        );
        return RefreshTokenMapper::toDomain($eloquent);
    }

    public function revokeRefreshToken(string $tokenValue): void
    {
        $refresh = $this->findByToken($tokenValue);

        if ($refresh) {
            $refresh=$this->update($refresh,['revoked' => true]);
        }
    }

    public function update(EntitiesRefreshToken $token, array $fields): EntitiesRefreshToken
    {
        $eloquentToken =  ModelsRefreshToken::findOrFail($token->id);
        $eloquentToken->update($fields);
        return RefreshTokenMapper::toDomain($eloquentToken);

    }


}
