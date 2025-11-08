<?php

namespace App\Core\Infraestructure\Repositories\Query;

use App\Core\Domain\Entities\Permission as EntitiesPermission;
use App\Core\Domain\Entities\Role as EntitiesRole;
use App\Core\Domain\Repositories\Query\RolesAndPermissosQueryRepInterface;
use App\Core\Infraestructure\Mappers\RolesAndPermissionMapper;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class EloquentRolesAndPermissionQueryRepository implements RolesAndPermissosQueryRepInterface
{
    public function findRoleById(int $id): EntitiesRole
    {
       return optional(Role::find($id),fn($role)=>RolesAndPermissionMapper::toRoleDomain($role));
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
    public function findAllPermissions(): array
    {
       return Permission::where('type', 'model')
        ->select('id', 'name', 'type')
        ->get()
        ->map(fn($permission) => RolesAndPermissionMapper::toPermissionDomain($permission))
        ->toArray();
    }
}
