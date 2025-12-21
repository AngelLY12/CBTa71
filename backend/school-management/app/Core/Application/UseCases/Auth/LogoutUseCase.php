<?php

namespace App\Core\Application\UseCases\Auth;

use App\Core\Domain\Enum\Cache\CachePrefix;
use App\Core\Domain\Enum\Cache\StudentCacheSufix;
use App\Core\Domain\Enum\User\UserRoles;
use App\Core\Domain\Repositories\Command\Auth\AccessTokenRepInterface;
use App\Core\Domain\Repositories\Command\Auth\RefreshTokenRepInterface;
use App\Core\Infraestructure\Cache\CacheService;
use App\Jobs\ClearParentCacheJob;
use App\Jobs\ClearStudentCacheJob;
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
        $userId = $user->id;

        if (in_array(UserRoles::PARENT->value, $roles)) {
            ClearParentCacheJob::dispatch($userId)->delay(now()->addSeconds(rand(1, 10)));
        }

        if (in_array(UserRoles::STUDENT->value, $roles)) {
            ClearStudentCacheJob::dispatch($userId)->delay(now()->addSeconds(rand(1, 10)));
            $this->service->clearPrefix(CachePrefix::STUDENT->value . StudentCacheSufix::CARDS->value . ":*:$userId");
        }
        $this->service->clearKey(CachePrefix::USER->value, $userId);

    }


}
