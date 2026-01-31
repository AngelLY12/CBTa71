<?php

namespace App\Core\Application\UseCases\Admin\RolePermissionManagement;

use App\Core\Application\DTO\Request\User\UpdateUserPermissionsDTO;
use App\Core\Application\DTO\Response\User\UserWithUpdatedPermissionsResponse;
use App\Core\Application\Mappers\UserMapper;
use App\Core\Domain\Repositories\Command\Auth\RolesAndPermissionsRepInterface;
use App\Core\Domain\Repositories\Query\Auth\RolesAndPermissosQueryRepInterface;
use App\Core\Domain\Repositories\Query\User\UserQueryRepInterface;
use App\Exceptions\NotFound\UsersNotFoundForUpdateException;
use App\Exceptions\Validation\ValidationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncPermissionsUseCase
{
    private const CHUNK_SIZE = 100;

    public function __construct(
        private RolesAndPermissionsRepInterface $repo,
        private RolesAndPermissosQueryRepInterface $rpqRepo,
        private UserQueryRepInterface $uqRepo,
    )
    {

    }

    public function execute(UpdateUserPermissionsDTO $dto): UserWithUpdatedPermissionsResponse
    {
        $this->validateNoDuplicatePermissions($dto);

        $usersGenerator = $this->getUsers($dto);

        $processedData = $this->processUsersFromGenerator($usersGenerator);

        if ($processedData['users']->isEmpty()) {
            throw new UsersNotFoundForUpdateException();
        }

        $result = $this->processPermissionsInTransaction($processedData['groupedByRole'], $dto);

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        return $this->buildResponse($processedData['users'], $dto, $result);
    }

    private function validateNoDuplicatePermissions(UpdateUserPermissionsDTO $dto): void
    {

        $add = $dto->permissionsToAdd ?? [];
        $remove = $dto->permissionsToRemove ?? [];

        $duplicates = array_intersect($add, $remove);

        if (!empty($duplicates)) {
            throw new ValidationException(
                "Los siguientes permisos no pueden estar simultáneamente en add y remove: "
                . implode(', ', $duplicates)
            );
        }

    }

    private function getUsers(UpdateUserPermissionsDTO $dto): \Generator
    {

        if ($dto->role) {
            yield from $this->uqRepo->getUsersByRoleCursor($dto->role);
        } elseif (is_array($dto->curps) && count($dto->curps) > 0) {
            yield from $this->uqRepo->getUsersByCurpCursor($dto->curps);
        } else {
            yield from [];
        }
    }


    private function processUsersFromGenerator(\Generator $usersGenerator): array
    {

        $usersGroupedByRole = collect();
        $allUsers = collect();
        $currentChunk = [];
        $hasUsers = false;

        foreach ($usersGenerator as $user) {
            $hasUsers = true;
            $currentChunk[] = $user;

            if (count($currentChunk) >= self::CHUNK_SIZE) {
                $this->processChunk(collect($currentChunk), $usersGroupedByRole, $allUsers);
                $currentChunk = [];
            }
        }

        if (!empty($currentChunk)) {
            $this->processChunk(collect($currentChunk), $usersGroupedByRole, $allUsers);
        }

        if (!$hasUsers) {
            throw new UsersNotFoundForUpdateException();
        }

        return [
            'groupedByRole' => $usersGroupedByRole,
            'users' => $allUsers
        ];
    }


    private function processChunk(Collection $chunk, Collection &$usersGroupedByRole, Collection &$allUsers): void
    {

        $allUsers = $allUsers->merge($chunk);

        $groupedChunk = $this->groupUsersByRole($chunk);

        foreach ($groupedChunk as $role => $users) {
            if ($usersGroupedByRole->has($role)) {
                $usersGroupedByRole[$role] = $usersGroupedByRole[$role]->merge($users);
            } else {
                $usersGroupedByRole[$role] = $users;
            }
        }

    }

    private function processPermissionsInTransaction(Collection $usersGroupedByRole, UpdateUserPermissionsDTO $dto): array
    {
        $totalResult = [
            'total_users' => 0,
            'users_affected_count' => 0,
            'users_unchanged_count' => 0,
            'users_failed_count' => 0,
            'permissions_removed' => 0,
            'permissions_added' => 0,
            'roles_processed' => 0,
            'users_affected' => [],
            'failed_users' => [],
            'unchanged_users' => [],
        ];
        $allUserIds = [];
        $permissionsByRole = [];
        foreach ($usersGroupedByRole as $role => $users) {
            $userIds = $users->pluck('id')->toArray();
            $allUserIds = array_merge($allUserIds, $userIds);
            $permissionsByRole[$role] = [
                'userIds' => $userIds,
                'addIds' => $this->getPermissionIds($dto->permissionsToAdd ?? [], $role),
                'removeIds' => $this->getPermissionIds($dto->permissionsToRemove ?? [], $role)
            ];
        }

        DB::transaction(function () use ($permissionsByRole, &$totalResult) {
            foreach ($permissionsByRole as $role => $data) {
                $userIds = $data['userIds'];
                $permissionsToAddIds = $data['addIds'];
                $permissionsToRemoveIds = $data['removeIds'];
                $changes = $this->repo->getUsersWithPermissionChanges(
                    $userIds,
                    $permissionsToAddIds,
                    $permissionsToRemoveIds,
                );

                $affectedIds = $changes['affected'];
                $unchangedIds = $changes['unchanged'];
                $totalResult['roles_processed']++;

                if (!empty($affectedIds)) {
                    if (!empty($permissionsToRemoveIds)) {
                        $totalResult['permissions_removed'] +=
                            $this->repo->removePermissions($affectedIds, $permissionsToRemoveIds);
                    }

                    if (!empty($permissionsToAddIds)) {
                        $totalResult['permissions_added'] +=
                            $this->repo->addPermissions($affectedIds, $permissionsToAddIds);
                    }
                }

                $totalResult['unchanged_users'] = array_values(array_unique(array_merge(
                    $totalResult['unchanged_users'],
                    $unchangedIds
                )));
                $totalResult['users_affected'] =array_values(array_unique(
                    array_merge(
                        $totalResult['users_affected'],
                        $affectedIds
                    )));

            }
        });
        $allUserIds = array_unique($allUserIds);
        $totalResult['users_affected_count'] = count(array_unique($totalResult['users_affected']));
        $totalResult['users_unchanged_count'] = count(array_unique($totalResult['unchanged_users']));
        $totalResult['total_users'] = count(array_unique($allUserIds));
        $totalResult['failed_users'] = array_diff(
            $allUserIds,
            array_merge(
                $totalResult['unchanged_users'],
                array_unique($totalResult['users_affected'])
            )
        );

        $totalResult['users_failed_count'] = count($totalResult['failed_users']);

        return $totalResult;
    }

    private function getPermissionIds(array $permissions, string $role): array
    {
        if (empty($permissions)) {
            return [];
        }

        return $this->rpqRepo->findPermissionIds($permissions, $role);
    }

    private function groupUsersByRole(Collection $users): Collection
    {

        return $users->flatMap(function ($user) {
            $roles = collect($user->roles);
            return $roles->map(fn($role) => [
                'role' => $role->name ?? $role,
                'user' => $user
            ]);
        })->groupBy('role')->map(fn($items) => $items->pluck('user'));
    }

    private function buildResponse(Collection $users, UpdateUserPermissionsDTO $dto, array $result): UserWithUpdatedPermissionsResponse
    {
        $permissions = [
            'added' => $dto->permissionsToAdd ?? [],
            'removed' => $dto->permissionsToRemove ?? [],
        ];

        return UserMapper::toUserUpdatedPermissionsResponse(
            summary: [
                'totalFound' => $result['total_users'],
                'totalUpdated'=> $result['users_affected_count'],
                'totalUnchanged' => $result['users_unchanged_count'],
                'totalFailed' => $result['users_failed_count'],
                'operations' => [
                    'permissions_removed' => $result['permissions_removed'],
                    'permissions_added' => $result['permissions_added'],
                    'roles_processed' => $result['roles_processed'],
                ]
            ],
            usersProcessed: [
                'processed_users' => array_slice(
                    array_diff(
                        $users->pluck('id')->toArray(),
                        array_merge($result['failed_users'], $result['unchanged_users'])
                    ),
                    0,
                    10
                ),
                'failed_users' => $result['failed_users'],
                'unchanged_users' => $result['unchanged_users'],
            ],
            updatedPermissions: $permissions
        );

    }
}
