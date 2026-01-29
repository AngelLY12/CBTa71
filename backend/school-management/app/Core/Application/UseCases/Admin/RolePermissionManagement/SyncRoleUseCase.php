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
use App\Exceptions\Validation\ValidationException;
use Illuminate\Support\Collection;

class SyncRoleUseCase
{
    private const CHUNK_SIZE = 100;
    public function __construct(
        private RolesAndPermissionsRepInterface $repo,
        private RolesAndPermissosQueryRepInterface $rpqRepo,
        private UserQueryRepInterface $uqRepo
    )
    {

    }

    public function execute(UpdateUserRoleDTO $dto): UserWithUpdatedRoleResponse
    {
        $this->validateNoDuplicateRoles($dto);
        $updated=$this->updateRoleToMany($dto);
        if ($updated === null)
        {
            throw new UsersNotFoundForUpdateException();
        }
        return $updated;
    }

    private function validateNoDuplicateRoles(UpdateUserRoleDTO $dto): void
    {
        $add = $dto->rolesToAdd ?? [];
        $remove = $dto->rolesToRemove ?? [];

        $duplicates = array_intersect($add, $remove);

        if (!empty($duplicates)) {
            throw new ValidationException(
                "Los siguientes roles no pueden estar simultáneamente en add y remove: "
                . implode(', ', $duplicates)
            );
        }
    }

    private function updateRoleToMany(UpdateUserRoleDTO $dto): ?UserWithUpdatedRoleResponse
    {
        if (empty($dto->curps) || (empty($dto->rolesToAdd) && empty($dto->rolesToRemove))) {
            return null;
        }

        $users = $this->uqRepo->getUsersByCurpCursor($dto->curps);

        $users = $this->processUsersFromGenerator($users);

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
            throw new RoleNotFoundException();
        }
        if ($this->hasAdminErrors($adminRole->id, $rolesToAddIds, $rolesToRemoveIds, $users)) {
            throw new AdminRoleNotAllowedException();
        }
        $rolesToRemoveIds = $this->handleUnverifiedRole($rolesToRemoveIds);
        $totalSync= $this->syncRolesInChunks($users, $rolesToAddIds, $rolesToRemoveIds);
        $data = $this->formatData($users, $dto, $totalSync);

        return UserMapper::toUserWithUptadedRoleResponse($data);

    }

    private function processUsersFromGenerator(\Generator $usersGenerator): Collection
    {
        $users = collect();
        $hasUsers = false;

        foreach ($usersGenerator as $user) {
            $hasUsers = true;
            $users->push($user);
        }

        return $hasUsers ? $users : collect();
    }

    private function hasAdminErrors(
        int $adminRoleId,
        array $rolesToAddIds,
        array $rolesToRemoveIds,
        Collection $users
    ): bool {
        return $this->rpqRepo->hasAdminAssignError($adminRoleId, $rolesToAddIds, $users)
            || $this->rpqRepo->hasAdminRemoveError($adminRoleId, $rolesToRemoveIds, $users)
            || $this->rpqRepo->hasAdminMissingError($adminRoleId, $rolesToRemoveIds, $rolesToAddIds);
    }

    private function handleUnverifiedRole(array $rolesToRemoveIds): array
    {
        $unverifiedRole = $this->rpqRepo->findRoleByName(UserRoles::UNVERIFIED->value);

        if ($unverifiedRole) {
            $rolesToRemoveIds[] = $unverifiedRole->id;
            $rolesToRemoveIds = array_unique($rolesToRemoveIds);
        }

        return $rolesToRemoveIds;
    }

    private function syncRolesInChunks(Collection $users, array $rolesToAddIds, array $rolesToRemoveIds): array
    {
        $totalResult = [
            'removed' => 0,
            'added' => 0,
            'users_affected' => 0,
            'total_chunks' => 0
        ];

        $callback = function (Collection $chunk) use ($rolesToAddIds, $rolesToRemoveIds, &$totalResult) {
            $resultado = $this->repo->syncRoles($chunk, $rolesToAddIds, $rolesToRemoveIds);

            $totalResult['removed'] += $resultado['removed'];
            $totalResult['added'] += $resultado['added'];
            $totalResult['users_affected'] += $resultado['users_affected'];
            $totalResult['total_chunks']++;
        };

        if ($users->count() > self::CHUNK_SIZE) {
            $users->chunk(self::CHUNK_SIZE)->each($callback);
        } else {
            $callback($users);
        }

        return $totalResult;
    }

    private function formatData(Collection $users, UpdateUserRoleDTO $dto,  array $totalSync): array
    {
        return [
            'names' => $users->map(fn($u) => "{$u->name} {$u->last_name}")->toArray(),
            'curps' => $users->take(10)->pluck('curp')->toArray(),
            'roles' => [
                'added' => $dto->rolesToAdd ?? [],
                'removed' => $dto->rolesToRemove ?? [],
            ],
            'metadata' => [
                'totalFound' => $users->count(),
                'totalUpdated' => $totalSync['users_affected'],
                'failed' => $users->count() - $totalSync['users_affected'],
                'operations' => [
                    'roles_removed' => $totalSync['removed'],
                    'roles_added' => $totalSync['added'],
                    'chunks_processed' => $totalSync['total_chunks'],
                ]
            ],
        ];
    }
}
