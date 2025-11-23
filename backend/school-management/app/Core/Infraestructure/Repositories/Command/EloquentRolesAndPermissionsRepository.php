<?php

namespace App\Core\Infraestructure\Repositories\Command;

use App\Core\Application\DTO\Request\User\UpdateUserPermissionsDTO;
use App\Core\Application\DTO\Request\User\UpdateUserRoleDTO;
use App\Core\Application\DTO\Response\User\UserWithUpdatedRoleResponse;
use App\Core\Domain\Repositories\Command\RolesAndPermissionsRepInterface;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Core\Application\Mappers\UserMapper as AppUserMapper;
use App\Models\User as EloquentUser;

class EloquentRolesAndPermissionsRepository implements RolesAndPermissionsRepInterface
{
    public function updatePermissionToMany(UpdateUserPermissionsDTO $dto): array
    {
        if (!empty($dto->role)) {
            $users = EloquentUser::role($dto->role)->get(['id', 'name', 'last_name', 'curp']);
        } elseif (!empty($dto->curps)) {
            $users = EloquentUser::whereIn('curp', $dto->curps)->get(['id', 'name', 'last_name', 'curp']);
        } else {
            return [];
        }
        if ($users->isEmpty()) {
            return [];
        }

        $usersGroupedByRole = $users->flatMap(function($user) {
            return $user->roles->map(fn($role) => ['role' => $role->name, 'user' => $user]);
        })->groupBy('role')
        ->map(fn($items) => $items->pluck('user'));


        DB::transaction(function () use ($usersGroupedByRole, $dto) {
            foreach ($usersGroupedByRole as $role => $usersOfRole) {

            $userIds = $usersOfRole->pluck('id')->toArray();
            $permissionsToAddIds = !empty($dto->permissionsToAdd)
                ? Permission::whereIn('name', $dto->permissionsToAdd)
                    ->where(function($q) use ($role) {
                        $q->where('belongs_to', $role)
                          ->orWhere('belongs_to', 'global');
                    })
                    ->pluck('id')
                    ->toArray()
                : [];

            $permissionsToRemoveIds = !empty($dto->permissionsToRemove)
                ? Permission::whereIn('name', $dto->permissionsToRemove)
                    ->where(function($q) use ($role) {
                        $q->where('belongs_to', $role)
                          ->orWhere('belongs_to', 'global');
                    })
                    ->pluck('id')
                    ->toArray()
                : [];

            if (!empty($permissionsToRemoveIds)) {
                DB::table('model_has_permissions')
                    ->whereIn('model_id', $userIds)
                    ->whereIn('permission_id', $permissionsToRemoveIds)
                    ->where('model_type', EloquentUser::class)
                    ->delete();
            }

            if (!empty($permissionsToAddIds)) {
                $rows = collect($userIds)->crossJoin($permissionsToAddIds)->map(fn($pair) => [
                    'model_id' => $pair[0],
                    'permission_id' => $pair[1],
                    'model_type' => EloquentUser::class,
                ])->toArray();

                DB::table('model_has_permissions')->insertOrIgnore($rows);
            }
        }

    });
    app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        $permissions =[
            'added' => $dto->permissionsToAdd ?? [],
            'removed' => $dto->permissionsToRemove ?? [],
        ];
        $totalUpdated = $users->count();
        if (!empty($dto->role)) {
            return [AppUserMapper::toUserUpdatedPermissionsResponse(permissions:$permissions, role:$dto->role, totalUpdated:$totalUpdated)];
        }

        return $users->map(fn($user) =>AppUserMapper::toUserUpdatedPermissionsResponse(user:$user, permissions:$permissions ,totalUpdated:$totalUpdated))->toArray();
    }

    public function updateRoleToMany(UpdateUserRoleDTO $dto): UserWithUpdatedRoleResponse
    {
        if(empty($dto->curps))
        {
            return new UserWithUpdatedRoleResponse([], [], ['added' => [], 'removed' => []], 0);
        }
        if (empty($dto->rolesToAdd) && empty($dto->rolesToRemove)) {
            return new UserWithUpdatedRoleResponse([], [], ['added' => [], 'removed' => []], 0);
        }
        $users =EloquentUser::whereIn('curp', $dto->curps)->get(['id', 'name', 'last_name', 'curp']);

        $rolesToAddIds=[];
        $rolesToRemoveIds=[];
        if(!empty($dto->rolesToAdd)){
            $rolesToAddIds = Role::whereIn('name', $dto->rolesToAdd)->pluck('id')->toArray();
        }
        if(!empty($dto->rolesToRemove))
        {
            $rolesToRemoveIds = Role::whereIn('name', $dto->rolesToRemove)->pluck('id')->toArray();
        }
        if (empty($rolesToAddIds) && empty($rolesToRemoveIds)) {
            return new UserWithUpdatedRoleResponse([], [], ['added' => [], 'removed' => []], 0);
        }

        DB::transaction(function () use ($users, $rolesToAddIds, $rolesToRemoveIds) {
            $userIds = $users->pluck('id')->toArray();
            if (!empty($rolesToRemoveIds)) {
                DB::table('model_has_roles')
                    ->whereIn('model_id', $userIds)
                    ->whereIn('role_id', $rolesToRemoveIds)
                    ->where('model_type', EloquentUser::class)
                    ->delete();
            }

            if (!empty($rolesToAddIds)) {
                $rows = [];
                foreach ($userIds as $userId) {
                    foreach ($rolesToAddIds as $roleId) {
                        $rows[] = [
                            'role_id' => $roleId,
                            'model_type' => EloquentUser::class,
                            'model_id' => $userId,
                        ];
                    }
                }

            DB::table('model_has_roles')->insertOrIgnore($rows);
        }


    });
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        $data =[
            'names' => $users->map(fn($user) => "{$user->name} {$user->last_name}")->toArray(),
            'curps' => $users->pluck('curp')->toArray(),
            'roles' => [
                'added' => $dto->rolesToAdd ?? [],
                'removed' => $dto->rolesToRemove ?? [],
            ],
            'totalUpdated' => $users->count()
        ];

        return AppUserMapper::toUserWithUptadedRoleResponse($data);
    }
}
