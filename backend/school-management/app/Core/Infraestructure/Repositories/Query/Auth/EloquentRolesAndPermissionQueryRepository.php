<?php

namespace App\Core\Infraestructure\Repositories\Query\Auth;

use App\Core\Domain\Entities\Permission as EntitiesPermission;
use App\Core\Domain\Entities\Role as EntitiesRole;
use App\Core\Domain\Enum\User\UserRoles;
use App\Core\Domain\Repositories\Query\Auth\RolesAndPermissosQueryRepInterface;
use App\Core\Infraestructure\Mappers\RolesAndPermissionMapper;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class EloquentRolesAndPermissionQueryRepository implements RolesAndPermissosQueryRepInterface
{
    public function findRoleById(int $id): ?EntitiesRole
    {
       return optional(Role::find($id),fn($role)=>RolesAndPermissionMapper::toRoleDomain($role));
    }
    public function findRoleByName(string $name): ?EntitiesRole
    {
        return optional(Role::where('name',$name)->first(),fn($role)=>RolesAndPermissionMapper::toRoleDomain($role));
    }
    public function findAllRoles(): array
    {
        return Role::select('id','name')
        ->where('hidden', false)
        ->get()
        ->map(fn($role)=>RolesAndPermissionMapper::toRoleDomain($role))
        ->toArray();
    }
    public function findPermissionById(int $id): ?EntitiesPermission
    {
        return optional(Permission::find($id),fn($permission)=>RolesAndPermissionMapper::toPermissionDomain($permission));
    }

    public function findPermissionsApplicableByUsers(?string $role, ?array $curps): array
    {
        $users = collect();

        if (!empty($role)) {
            try {
                $users = User::role($role)
                    ->with('roles')
                    ->get(['curp', 'id']);
            } catch (\Spatie\Permission\Exceptions\RoleDoesNotExist $e) {
                return [];
            }
        } elseif (!empty($curps)) {
            $users = User::with('roles')
                ->whereIn('curp', $curps)
                ->get(['curp', 'id']);
        }

        if ($users->isEmpty()) return [];

        $usersGroupedByRole = $users->flatMap(function($user) {
            return $user->roles->map(fn($role) => ['role' => $role->name, 'user' => $user]);
        })->groupBy('role')
        ->map(fn($items) => $items->pluck('user'));

        $result = [];
        foreach ($usersGroupedByRole as $roleName => $usersOfRole) {
            $permissions = Permission::where('type', 'model')
                ->where(function($q) use ($roleName) {
                    $q->where('belongs_to', $roleName)
                    ->orWhere('belongs_to', 'global-payment');
                    if ($roleName === UserRoles::SUPERVISOR->value) {
                        $q->orWhere('belongs_to', 'administration');
                    }
                    if($roleName === UserRoles::STUDENT->value){
                        $q->orWhere('belongs_to', $roleName . '-payment');
                    }
                })
                ->select('id','name','type')
                ->get()
                ->map(fn($permission) => RolesAndPermissionMapper::toPermissionDomain($permission));
            $usersCount = $usersOfRole->count();
            $result[] = [
                'role' => $roleName,
                'users' => [
                    'count' => $usersCount,
                    'curps' => $usersCount <= 15
                        ? $usersOfRole->pluck('curp')->values()->toArray()
                        : []
                ],
                'permissions' => $permissions->toArray()
            ];
        }

        return $result;
    }

    public function findPermissionIds(array $names, string $role): array
    {
        return Permission::whereIn('name', $names)
            ->where(function($q) use ($role) {
                $q->where('belongs_to', $role)
                  ->orWhere('belongs_to', 'global-payment');
                  if ($role === UserRoles::SUPERVISOR->value) {
                        $q->orWhere('belongs_to', 'administration');
                  }
                if($role === UserRoles::STUDENT->value){
                    $q->orWhere('belongs_to', $role . '-payment');
                }
            })
            ->pluck('id')
            ->toArray();
    }

    public function getRoleIdsByNames(array $names): array
    {
        return Role::whereIn('name', $names)->pluck('id')->toArray();
    }

    public function hasAdminAssignError(int $adminRoleId, array $rolesToAddIds, Collection $users): bool
    {
        if (!in_array($adminRoleId, $rolesToAddIds)) return false;

        $existingAdmin = DB::table('model_has_roles')
            ->where('role_id', $adminRoleId)
            ->where('model_type', User::class)
            ->first();

        if (!$existingAdmin) return false;

        $targetIds = $users->pluck('id')->toArray();
        return !in_array($existingAdmin->model_id, $targetIds);
    }

    public function hasAdminRemoveError(int $adminRoleId, array $rolesToRemoveIds, Collection $users): bool
    {
        if (!in_array($adminRoleId, $rolesToRemoveIds)) return false;

        $currentAdmins = DB::table('model_has_roles')
            ->where('role_id', $adminRoleId)
            ->where('model_type', User::class)
            ->pluck('model_id')
            ->toArray();

        $targetIds = $users->pluck('id')->toArray();

        return !empty(array_diff($currentAdmins, $targetIds));
    }

    public function hasAdminMissingError(int $adminRoleId, array $rolesToRemoveIds, array $rolesToAddIds): bool
    {
        if (!in_array($adminRoleId, $rolesToRemoveIds)) {
            return false;
        }
        $hasReplacement = in_array($adminRoleId, $rolesToAddIds);
        if ($hasReplacement) {
            return false;
        }

        $adminCount = DB::table('model_has_roles')
            ->where('role_id', $adminRoleId)
            ->where('model_type', User::class)
            ->count();

        return $adminCount <= 1;
    }

}
