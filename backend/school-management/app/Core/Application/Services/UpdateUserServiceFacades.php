<?php

namespace App\Core\Application\Services;

use App\Core\Application\Traits\HasCache;
use App\Core\Application\UseCases\UpdateUserUseCase;
use App\Core\Domain\Entities\User;
use App\Core\Infraestructure\Cache\CacheService;
use Illuminate\Support\Facades\Hash;

class UpdateUserServiceFacades
{
    use HasCache;

    private string $prefix = 'user';

    public function __construct(private UpdateUserUseCase $update,
            private CacheService $service
)
    {
    }

    public function updateUser(int $userId, array $fields): User
    {
        $updatedUser = $this->update->execute($userId, $fields);
        $this->service->forget("$this->prefix:$userId");
        return $updatedUser;
    }

    public function updatePassword(int $userId, string $newPassword): User
    {
        $hashed = Hash::make($newPassword);
        $updatedUser = $this->update->execute($userId, ['password' => $hashed]);
        $this->service->forget("$this->prefix:$userId");
        return $updatedUser;
    }
}
