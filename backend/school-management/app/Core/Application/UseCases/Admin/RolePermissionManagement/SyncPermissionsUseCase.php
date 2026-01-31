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

    public function execute(UpdateUserPermissionsDTO $dto): array
    {
        Log::info('=== INICIO execute ===', ['dto' => $dto]);

        $this->validateNoDuplicatePermissions($dto);

        $usersGenerator = $this->getUsers($dto);
        Log::info('✅ Generator obtenido', ['generator' => $usersGenerator]);

        $processedData = $this->processUsersFromGenerator($usersGenerator);

        if ($processedData['users']->isEmpty()) {
            Log::error('❌ No se encontraron usuarios procesados', ['processedData' => $processedData]);
            throw new UsersNotFoundForUpdateException();
        }

        $result = $this->processPermissionsInTransaction($processedData['groupedByRole'], $dto);

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        $response = $this->buildResponse($processedData['users'], $dto, $result);
        Log::info('✅ execute finalizado', ['response' => $response]);

        return $response;
    }

    private function validateNoDuplicatePermissions(UpdateUserPermissionsDTO $dto): void
    {
        Log::info('1. Validando permisos duplicados', ['dto' => $dto]);

        $add = $dto->permissionsToAdd ?? [];
        $remove = $dto->permissionsToRemove ?? [];

        $duplicates = array_intersect($add, $remove);

        if (!empty($duplicates)) {
            Log::error('❌ Permisos duplicados encontrados', ['duplicates' => $duplicates]);
            throw new ValidationException(
                "Los siguientes permisos no pueden estar simultáneamente en add y remove: "
                . implode(', ', $duplicates)
            );
        }

        Log::info('✅ Sin permisos duplicados');
    }

    private function getUsers(UpdateUserPermissionsDTO $dto): \Generator
    {
        Log::info('--- INICIO getUsers ---', [
            'dto_role' => $dto->role,
            'dto_curps' => $dto->curps
        ]);

        if ($dto->role) {
            $generator = $this->uqRepo->getUsersByRoleCursor($dto->role);
            Log::info('🔍 Obteniendo usuarios por role', ['role' => $dto->role]);
            yield from $generator;
        } elseif (is_array($dto->curps) && count($dto->curps) > 0) {
            $generator = $this->uqRepo->getUsersByCurpCursor($dto->curps);
            Log::info('🔍 Obteniendo usuarios por CURPs', ['curps' => $dto->curps]);
            yield from $generator;
        } else {
            Log::info('⚠ No se recibieron CURPs ni role, generator vacío');
            yield from [];
        }
    }


    private function processUsersFromGenerator(\Generator $usersGenerator): array
    {
        Log::info('--- INICIO processUsersFromGenerator ---');

        $usersGroupedByRole = collect();
        $allUsers = collect();
        $currentChunk = [];
        $hasUsers = false;

        foreach ($usersGenerator as $user) {
            $hasUsers = true;
            $currentChunk[] = $user;
            Log::debug('Usuario agregado al chunk', ['user_id' => $user->id, 'chunk_size' => count($currentChunk)]);

            if (count($currentChunk) >= self::CHUNK_SIZE) {
                $this->processChunk(collect($currentChunk), $usersGroupedByRole, $allUsers);
                $currentChunk = [];
            }
        }

        if (!empty($currentChunk)) {
            $this->processChunk(collect($currentChunk), $usersGroupedByRole, $allUsers);
        }

        Log::info('Total de usuarios procesados', ['count' => $allUsers->count()]);

        if (!$hasUsers) {
            Log::error('❌ No se encontraron usuarios en generator');
            throw new UsersNotFoundForUpdateException();
        }

        return [
            'groupedByRole' => $usersGroupedByRole,
            'users' => $allUsers
        ];
    }


    private function processChunk(Collection $chunk, Collection &$usersGroupedByRole, Collection &$allUsers): void
    {
        Log::debug('--- processChunk ---', [
            'chunk_count' => $chunk->count(),
            'allUsers_before' => $allUsers->count()
        ]);

        $allUsers = $allUsers->merge($chunk);
        Log::debug('Usuarios totales después de merge', ['allUsers_after' => $allUsers->count()]);

        $groupedChunk = $this->groupUsersByRole($chunk);

        Log::debug('Usuarios agrupados por rol en chunk', ['groupedChunk' => $groupedChunk->map(fn($c) => $c->pluck('id'))]);

        foreach ($groupedChunk as $role => $users) {
            if ($usersGroupedByRole->has($role)) {
                $usersGroupedByRole[$role] = $usersGroupedByRole[$role]->merge($users);
            } else {
                $usersGroupedByRole[$role] = $users;
            }
        }

        Log::debug('Usuarios agrupados por rol totales', ['usersGroupedByRole' => $usersGroupedByRole->map(fn($c) => $c->pluck('id'))]);
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
        Log::debug('--- groupUsersByRole ---', ['users_count' => $users->count()]);

        return $users->flatMap(function ($user) {
            $roles = collect($user->roles); // convierte a Collection por seguridad
            Log::debug('Roles del usuario', ['user_id' => $user->id, 'roles' => $roles->pluck('name')]);
            return $roles->map(fn($role) => [
                'role' => $role->name ?? $role,
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
