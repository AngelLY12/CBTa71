<?php

namespace App\Core\Application\UseCases\Admin;

use App\Core\Application\DTO\Request\User\UpdateUserPermissionsDTO;
use App\Core\Application\Mappers\UserMapper;
use App\Core\Domain\Repositories\Command\Auth\RolesAndPermissionsRepInterface;
use App\Core\Domain\Repositories\Query\Auth\RolesAndPermissosQueryRepInterface;
use App\Core\Domain\Repositories\Query\User\UserQueryRepInterface;
use App\Exceptions\NotFound\UsersNotFoundForUpdateException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SyncPermissionsUseCase
{
    public function __construct(
        private RolesAndPermissionsRepInterface $repo,
        private RolesAndPermissosQueryRepInterface $rpqRepo,
        private UserQueryRepInterface $uqRepo,
    )
    {

    }

    public function execute(UpdateUserPermissionsDTO $dto): array
    {
        $updated=$this->updatePermissionToMany($dto);
        if (empty($updated))
        {
            throw new UsersNotFoundForUpdateException();
        }
        return $updated;
    }

    private function updatePermissionToMany(UpdateUserPermissionsDTO $dto):array
    {
        $users = !empty($dto->role)
            ? $this->uqRepo->getUsersByRole($dto->role)
            : (!empty($dto->curps)
                ? $this->uqRepo->getUsersByCurp($dto->curps)
                : collect());

        if ($users->isEmpty()) {
            throw new UsersNotFoundForUpdateException();
        }

        $usersGroupedByRole = $this->groupUsersByRole($users);

        DB::transaction(function () use ($usersGroupedByRole, $dto) {
            foreach ($usersGroupedByRole as $role => $usersOfRole) {
                $userIds = $usersOfRole->pluck('id')->toArray();

                $permissionsToAddIds = !empty($dto->permissionsToAdd)
                    ? $this->rpqRepo->findPermissionIds($dto->permissionsToAdd, $role)
                    : [];

                $permissionsToRemoveIds = !empty($dto->permissionsToRemove)
                    ? $this->rpqRepo->findPermissionIds($dto->permissionsToRemove, $role)
                    : [];

                $this->repo->removePermissions($userIds, $permissionsToRemoveIds);
                $this->repo->addPermissions($userIds, $permissionsToAddIds);
            }
        });

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'added' => $dto->permissionsToAdd ?? [],
            'removed' => $dto->permissionsToRemove ?? [],
        ];

        $totalUpdated = $users->count();

        return !empty($dto->role)
            ? [UserMapper::toUserUpdatedPermissionsResponse(permissions: $permissions, role: $dto->role, totalUpdated: $totalUpdated)]
            : $users->map(fn($user) => UserMapper::toUserUpdatedPermissionsResponse(user: $user, permissions: $permissions, totalUpdated: $totalUpdated))->toArray();

    }

    private function groupUsersByRole(Collection $users): Collection
    {
        return $users->flatMap(fn($user) =>
            $user->roles->map(fn($role) => ['role' => $role->name, 'user' => $user])
        )->groupBy('role')->map(fn($items) => $items->pluck('user'));
    }
}
