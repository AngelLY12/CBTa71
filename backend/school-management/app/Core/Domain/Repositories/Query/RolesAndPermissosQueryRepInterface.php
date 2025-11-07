<?php

namespace App\Core\Domain\Repositories\Query;

use App\Core\Domain\Entities\Permission;
use App\Core\Domain\Entities\Role;
use App\Core\Domain\Entities\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface RolesAndPermissosQueryRepInterface
{
    public function findRoleById(int $id):?Role;
    public function findAllRoles(): array;
    public function findPermissionById(int $id):?Permission;
    public function findAllPermissions(int $page): LengthAwarePaginator;
}
