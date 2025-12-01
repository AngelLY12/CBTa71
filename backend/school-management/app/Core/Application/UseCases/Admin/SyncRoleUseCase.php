<?php

namespace App\Core\Application\UseCases\Admin;

use App\Core\Application\DTO\Request\User\UpdateUserRoleDTO;
use App\Core\Application\DTO\Response\User\UserWithUpdatedRoleResponse;
use App\Core\Application\Mappers\UserMapper;
use App\Core\Domain\Enum\User\UserRoles;
use App\Core\Domain\Repositories\Command\Auth\RolesAndPermissionsRepInterface;
use App\Core\Domain\Repositories\Query\Auth\RolesAndPermissosQueryRepInterface;
use App\Core\Domain\Repositories\Query\User\UserQueryRepInterface;
use App\Exceptions\NotAllowed\AdminRoleNotAllowedException;
use App\Exceptions\NotFound\RoleNotFoundException;
use App\Exceptions\NotFound\UsersNotFoundForUpdateException;
use Illuminate\Support\Collection;

class SyncRoleUseCase
{
    public function __construct(
        private RolesAndPermissionsRepInterface $repo,
        private RolesAndPermissosQueryRepInterface $rpqRepo,
        private UserQueryRepInterface $uqRepo
    )
    {

    }

    public function execute(UpdateUserRoleDTO $dto): UserWithUpdatedRoleResponse
    {
        $updated=$this->updateRoleToMany($dto);
        if (empty($updated))
        {
            throw new UsersNotFoundForUpdateException();
        }
        return $updated;
    }

    private function updateRoleToMany(UpdateUserRoleDTO $dto): ?UserWithUpdatedRoleResponse
    {
        if (empty($dto->curps) || (empty($dto->rolesToAdd) && empty($dto->rolesToRemove))) {
            return null;
        }

        $users = $this->uqRepo->getUsersByCurp($dto->curps);

        if ($users->isEmpty()) {
            return null;
        }
        $rolesToAddIds = $this->rpqRepo->getRoleIdsByNames($dto->rolesToAdd);
        $rolesToRemoveIds = $this->rpqRepo->getRoleIdsByNames($dto->rolesToRemove);

        if (empty($rolesToAddIds) && empty($rolesToRemoveIds)) {
            throw new RoleNotFoundException();
        }

        $adminRole = $this->rpqRepo->findRoleByName(UserRoles::ADMIN->value);
        if (!$adminRole) {
            throw new RoleNotFoundException('Admin role not found.');
        }
        if ($this->rpqRepo->hasAdminAssignError($adminRole->id, $rolesToAddIds, $users)
            || $this->rpqRepo->hasAdminRemoveError($adminRole->id, $rolesToRemoveIds, $users)
            || $this->rpqRepo->hasAdminMissingError($adminRole->id, $rolesToRemoveIds, $rolesToAddIds)
        ) {
            throw new AdminRoleNotAllowedException();
        }

        $unverifiedRole = $this->rpqRepo->findRoleByName(UserRoles::UNVERIFIED->value);
        if ($unverifiedRole) {
            $rolesToRemoveIds[] = $unverifiedRole->id;
            $rolesToRemoveIds = array_unique($rolesToRemoveIds);
        }
        $this->repo->syncRoles($users, $rolesToAddIds, $rolesToRemoveIds);
        $data = $this->formatData($users, $dto);

        return UserMapper::toUserWithUptadedRoleResponse($data);

    }

    private function formatData(Collection $users, UpdateUserRoleDTO $dto): array
    {
        return [
            'names' => $users->map(fn($u) => "{$u->name} {$u->last_name}")->toArray(),
            'curps' => $users->pluck('curp')->toArray(),
            'roles' => [
                'added' => $dto->rolesToAdd ?? [],
                'removed' => $dto->rolesToRemove ?? [],
            ],
            'totalUpdated' => $users->count(),
        ];
    }
}
