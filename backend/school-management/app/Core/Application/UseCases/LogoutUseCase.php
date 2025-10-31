<?php

namespace App\Core\Application\UseCases;

use App\Core\Domain\Repositories\Command\RefreshTokenRepInterface;
use App\Core\Domain\Repositories\Command\UserRepInterface;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LogoutUseCase
{
    public function __construct(
        private RefreshTokenRepInterface $refresh,
        private UserRepInterface $userRepo
    )
    {
    }
    public function execute(User $user, ?string $refreshTokenValue): void
    {
        DB::transaction(function () use ($user, $refreshTokenValue) {
            $accessToken = $user->currentAccessToken();
            if ($accessToken) {
                $this->userRepo->revokeToken($accessToken->id);
            }

            if ($refreshTokenValue) {
                $this->refresh->revokeRefreshToken($refreshTokenValue);
            }
        });

    }

}
