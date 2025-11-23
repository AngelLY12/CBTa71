<?php

namespace App\Core\Application\UseCases;

use App\Core\Domain\Repositories\Command\AccessTokenRepInterface;
use App\Core\Domain\Repositories\Command\RefreshTokenRepInterface;
use App\Core\Infraestructure\Cache\CacheService;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LogoutUseCase
{
    public function __construct(
        private RefreshTokenRepInterface $refresh,
        private AccessTokenRepInterface $access,
        private CacheService $service
    )
    {
    }
    public function execute(User $user, ?string $refreshTokenValue): void
    {
        DB::transaction(function () use ($user, $refreshTokenValue) {
            $accessToken = $user->currentAccessToken();
            if ($accessToken) {
                $this->access->revokeToken($accessToken->id);
            }

            if ($refreshTokenValue) {
                $this->refresh->revokeRefreshToken($refreshTokenValue);
            }
        });
        $roles = $user->roles()->pluck('name')->toArray();

        if (in_array('admin', $roles)) {
            $this->service->clearPrefix("admin");
        }

        if (in_array('student', $roles)) {
            $userId = $user->id;
            $this->service->clearPrefix("student:dashboard-user:*:$userId");
            $this->service->clearPrefix("student:pending:*:$userId");
            $this->service->clearPrefix("student:history:$userId");
            $this->service->clearPrefix("student:cards:*:$userId");
        }

    }

}
