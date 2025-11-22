<?php

namespace App\Core\Infraestructure\Repositories\Query;

use App\Core\Domain\Entities\Permission as EntitiesPermission;
use App\Core\Domain\Entities\Role as EntitiesRole;
use App\Core\Domain\Repositories\Query\RolesAndPermissosQueryRepInterface;
use App\Core\Infraestructure\Mappers\RolesAndPermissionMapper;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class EloquentRolesAndPermissionQueryRepository implements RolesAndPermissosQueryRepInterface
{
    public function findRoleById(int $id): EntitiesRole
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
        ->get()
        ->map(fn($role)=>RolesAndPermissionMapper::toRoleDomain($role))
        ->toArray();
    }
    public function findPermissionById(int $id): EntitiesPermission
    {
        return optional(Permission::find($id),fn($permission)=>RolesAndPermissionMapper::toPermissionDomain($permission));
    }

    public function findPermissionsApplicableByUsers(array $curps): array
    {
        $users = User::with('roles')->whereIn('curp', $curps)
        ->get(['id','name','last_name','curp']);

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
                    ->orWhere('belongs_to', 'global');
                })
                ->select('id','name','type')
                ->get()
                ->map(fn($permission) => RolesAndPermissionMapper::toPermissionDomain($permission));

            $result[] = [
                'role' => $roleName,
                'users' => $usersOfRole->map(fn($u) => [
                    'id' => $u->id,
                    'fullName' => $u->name . ' ' . $u->last_name,
                    'curp' => $u->curp
                ])->toArray(),
                'permissions' => $permissions->toArray()
            ];
        }

        return $result;
    }
}
