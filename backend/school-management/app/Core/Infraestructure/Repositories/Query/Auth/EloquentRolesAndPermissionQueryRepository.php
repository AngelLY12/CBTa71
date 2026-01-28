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
        if (empty($role) && (is_null($curps) || empty($curps))) {
            return [];
        }

        $query = User::query()->with('roles:name');

        if (!empty($role) && (is_null($curps) || empty($curps))) {
            try {
                $query->role($role);
            } catch (\Spatie\Permission\Exceptions\RoleDoesNotExist $e) {
                return [];
            }
        } elseif ((is_null($role) || empty($role)) && !empty($curps)) {
            $query->whereIn('curp', $curps)
            ->whereHas('roles');
        }

        $totalUsers = $query->count();
        if ($totalUsers === 0) return [];
        $limitUsers = min($totalUsers, 15);
        $displayed = $totalUsers <= 15 ? $totalUsers : 0;

        $usersWithRoles = $query->with(['roles:name'])
            ->limit($limitUsers)
            ->get(['curp', 'id'])
            ->map(function($user) {
                return [
                    'id' => $user->id,
                    'curp' => $user->curp,
                    'roles' => $user->roles->pluck('name')->toArray()
                ];
            });

        $userItems = $usersWithRoles->toArray();

        $allUsersData = [
            'total' => $totalUsers,
            'displayed' => $displayed,
            'items' => $totalUsers > 15 ? [] : $userItems
        ];

        $allRoleNames = collect($userItems)
            ->flatMap(fn($user) => $user['roles'])
            ->unique()
            ->values()
            ->toArray();

        if (empty($allRoleNames) && $totalUsers > 0) {
            $allRoleNames = Role::whereHas('users', function($q) use ($query) {
                $q->whereIn('users.id', $query->clone()->select('users.id'));
            })
                ->pluck('name')
                ->toArray();
        }
        $permissionsByRole = [];
        if (!empty($allRoleNames)) {
            foreach ($allRoleNames as $roleName) {
                $permissions = Permission::where('type', 'model')
                    ->where(function($q) use ($roleName) {
                        $q->where('belongs_to', $roleName)
                            ->orWhere('belongs_to', 'global-payment');
                        if ($roleName === UserRoles::SUPERVISOR->value) {
                            $q->orWhere('belongs_to', 'administration');
                        }
                        if ($roleName === UserRoles::STUDENT->value ||
                            $roleName === UserRoles::APPLICANT->value) {
                            $q->orWhere('belongs_to', $roleName . '-payment');
                        }
                    })
                    ->select('id', 'name', 'type')
                    ->get()
                    ->map(fn($permission) => RolesAndPermissionMapper::toPermissionDomain($permission))
                    ->toArray();

                $permissionsByRole[] = [
                    'role' => $roleName,
                    'permissions' => $permissions
                ];
            }
        }

        return [
            'role' => $role,
            'users' => $allUsersData,
            'permissions' => $permissionsByRole
        ];

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
