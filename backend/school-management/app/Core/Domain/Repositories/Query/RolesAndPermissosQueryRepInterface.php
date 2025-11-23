<?php

namespace App\Core\Domain\Repositories\Query;

use App\Core\Domain\Entities\Permission;
use App\Core\Domain\Entities\Role;

interface RolesAndPermissosQueryRepInterface
{
    public function findRoleById(int $id):?Role;
    public function findRoleByName(string $name): ?Role;
    public function findAllRoles(): array;
    public function findPermissionById(int $id):?Permission;
    public function findPermissionsApplicableByUsers(?string $role, ?array $curps): array;
}
