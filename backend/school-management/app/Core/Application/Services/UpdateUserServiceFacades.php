<?php

namespace App\Core\Application\Services;

use App\Core\Application\Traits\HasCache;
use App\Core\Application\UseCases\UpdateUserUseCase;
use App\Core\Domain\Entities\User;
use App\Core\Domain\Repositories\Query\UserQueryRepInterface;
use App\Core\Domain\Utils\Validators\UserValidator;
use App\Core\Infraestructure\Cache\CacheService;
use App\Exceptions\NotAllowed\InvalidCurrentPasswordException;
use Illuminate\Support\Facades\Hash;

class UpdateUserServiceFacades
{
    use HasCache;

    private string $prefix = 'user';

    public function __construct(private UpdateUserUseCase $update,
            private CacheService $service,
            private UserQueryRepInterface $userRepo
)
    {
    }

    public function updateUser(int $userId, array $fields): User
    {
        $user = $this->userRepo->findById($userId);
        UserValidator::ensureUserIsValidToUpdate($user);
        $updatedUser = $this->update->execute($userId, $fields);
        $this->service->clearPrefix("$this->prefix:$userId");
        return $updatedUser;
    }

    public function updatePassword(int $userId, string $currentPassword, string $newPassword): User
    {
        $user = $this->userRepo->findById($userId);
        UserValidator::ensureUserIsValidToUpdate($user);
        if (!Hash::check($currentPassword, $user->password)) {
            throw new InvalidCurrentPasswordException();
        }
        $hashed = Hash::make($newPassword);
        $updatedUser = $this->update->execute($userId, ['password' => $hashed]);
        $this->service->clearPrefix("$this->prefix:$userId");
        return $updatedUser;
    }
}
