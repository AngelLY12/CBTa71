<?php

namespace App\Core\Application\UseCases\Admin\RolePermissionManagement;

use App\Core\Application\DTO\Request\User\UpdateUserPermissionsDTO;
use App\Core\Application\Mappers\UserMapper;
use App\Core\Domain\Repositories\Command\Auth\RolesAndPermissionsRepInterface;
use App\Core\Domain\Repositories\Query\Auth\RolesAndPermissosQueryRepInterface;
use App\Core\Domain\Repositories\Query\User\UserQueryRepInterface;
use App\Exceptions\NotFound\UsersNotFoundForUpdateException;
use App\Exceptions\Validation\ValidationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

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

    public function execute(UpdateUserPermissionsDTO $dto): array
    {
        $this->validateNoDuplicatePermissions($dto);
        $usersGenerator = $this->getUsers($dto);
        $processedData = $this->processUsersFromGenerator($usersGenerator);

        if ($processedData['users']->isEmpty()) {
            throw new UsersNotFoundForUpdateException();
        }

        $result=$this->processPermissionsInTransaction($processedData['groupedByRole'], $dto);

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
            return $this->uqRepo->getUsersByRoleCursor($dto->role);
        }

        if (is_array($dto->curps) && count($dto->curps) > 0) {
            return $this->uqRepo->getUsersByCurpCursor($dto->curps);
        }

        yield from [];
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
            'permissions_removed' => 0,
            'permissions_added' => 0,
            'roles_processed' => 0,
            'users_affected' => 0,
            'failed_users' => []
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

        $allUserIds = array_unique($allUserIds);
        $currentPermissions = $this->repo->getUsersPermissions($allUserIds);

        DB::transaction(function () use ($permissionsByRole, $currentPermissions, &$totalResult) {
            foreach ($permissionsByRole as $role => $data) {
                $userIds = $data['userIds'];
                $permissionsToAddIds = $data['addIds'];
                $permissionsToRemoveIds = $data['removeIds'];

                $totalResult['total_users'] += count($userIds);
                $totalResult['roles_processed']++;

                if (!empty($permissionsToRemoveIds)) {
                    $removed = $this->repo->removePermissions($userIds, $permissionsToRemoveIds);
                    $totalResult['permissions_removed'] += $removed;
                }

                if (!empty($permissionsToAddIds)) {
                    $added = $this->repo->addPermissions($userIds, $permissionsToAddIds);
                    $totalResult['permissions_added'] += $added;
                }

                $affectedUsers = $this->verifyAffectedUsers(
                    $userIds,
                    $permissionsToAddIds,
                    $permissionsToRemoveIds,
                    $currentPermissions
                );

                $totalResult['users_affected'] += $affectedUsers['affected'];
                $totalResult['failed_users'] = array_merge(
                    $totalResult['failed_users'],
                    $affectedUsers['failed']
                );
            }
        });

        return $totalResult;
    }

    private function verifyAffectedUsers(
        array $userIds,
        array $permissionsToAddIds,
        array $permissionsToRemoveIds,
        array $currentPermissions
    ): array {
        $result = [
            'affected' => 0,
            'failed' => []
        ];

        if (empty($permissionsToAddIds) && empty($permissionsToRemoveIds)) {
            return $result;
        }

        $permissionsToAddMap = array_flip($permissionsToAddIds);
        $permissionsToRemoveMap = array_flip($permissionsToRemoveIds);

        foreach ($userIds as $userId) {
            $permissionsStr = $currentPermissions[$userId] ?? '';
            $currentPerms = $permissionsStr ? explode(',', $permissionsStr) : [];
            $currentPermsMap = array_flip($currentPerms);

            $totalAdded = empty(array_diff_key($permissionsToAddMap, $currentPermsMap));
            $totalRemoved = empty(array_intersect_key($permissionsToRemoveMap, $currentPermsMap));

            if ($totalAdded && $totalRemoved) {
                $result['affected']++;
            } else {
                $result['failed'][] = $userId;
            }
        }

        return $result;

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
            return $user->roles->map(fn($role) => [
                'role' => $role->name,
                'user' => $user
            ]);
        })->groupBy('role')->map(fn($items) => $items->pluck('user'));
    }

    private function buildResponse(Collection $users, UpdateUserPermissionsDTO $dto, array $result): array
    {
        $permissions = [
            'added' => $dto->permissionsToAdd ?? [],
            'removed' => $dto->permissionsToRemove ?? [],
        ];


        if (!empty($dto->role)) {
            return [UserMapper::toUserUpdatedPermissionsResponse(
                permissions: $permissions,
                metadata: [
                    'totalFound' => $result['total_users'],
                    'totalUpdated'=> $result['users_affected'],
                    'failed' => $result['total_users'] - $result['users_affected'],
                    'operations' => [
                        'permissions_removed' => $result['permissions_removed'],
                        'permissions_added' => $result['permissions_added'],
                        'roles_processed' => $result['roles_processed'],
                    ]
                ],
                role: $dto->role
            )];
        }

        return $users->take(10)
            ->map(fn($user) => UserMapper::toUserUpdatedPermissionsResponse(
                permissions: $permissions,
                metadata: [
                    'totalFound' => $result['total_users'],
                    'totalUpdated'=> $result['users_affected'],
                    'failed' => $result['total_users'] - $result['users_affected'],
                    'failedUsers' => array_slice($result['failed_users'], 0, 10),
                    'operations' => [
                        'permissions_removed' => $result['permissions_removed'],
                        'permissions_added' => $result['permissions_added'],
                        'roles_processed' => $result['roles_processed'],
                    ]
                ],
                user: $user
            ))
            ->toArray();
    }
}
