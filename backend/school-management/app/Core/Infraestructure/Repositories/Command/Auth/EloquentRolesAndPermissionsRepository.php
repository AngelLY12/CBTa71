<?php

namespace App\Core\Infraestructure\Repositories\Command\Auth;

use App\Core\Domain\Repositories\Command\Auth\RolesAndPermissionsRepInterface;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use App\Models\User as EloquentUser;
use Illuminate\Support\Collection;

class EloquentRolesAndPermissionsRepository implements RolesAndPermissionsRepInterface
{

    public function assignRoles(array $roleRows): void {
        if (!empty($roleRows)) {
            DB::table('model_has_roles')->insertOrIgnore($roleRows);
        }
    }


    public function givePermissionsByType(EloquentUser $user, string $belongsTo, string $type = 'model'): void
    {
        $permissions = Permission::where('belongs_to', $belongsTo)
            ->where('type', $type)
            ->pluck('name')
            ->toArray();

        $user->givePermissionTo($permissions);
    }

    public function removePermissions(array $userIds, array $permissionIds): void
    {
        if (!empty($permissionIds) && !empty($userIds)) {
            DB::table('model_has_permissions')
                ->whereIn('model_id', $userIds)
                ->whereIn('permission_id', $permissionIds)
                ->where('model_type', EloquentUser::class)
                ->delete();
        }
    }

    public function addPermissions(array $userIds, array $permissionIds): void
    {
        if (!empty($permissionIds) && !empty($userIds)) {
            $rows = collect($userIds)->crossJoin($permissionIds)->map(fn($pair) => [
                'model_id' => $pair[0],
                'permission_id' => $pair[1],
                'model_type' => EloquentUser::class,
            ])->toArray();
            DB::table('model_has_permissions')->insertOrIgnore($rows);
        }
    }

    public function syncRoles(Collection $users, array $rolesToAddIds, array $rolesToRemoveIds): void
    {
        $userIds = $users->pluck('id')->toArray();

        DB::transaction(function () use ($userIds, $rolesToAddIds, $rolesToRemoveIds) {
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
    }
}
