<?php

namespace App\Core\Domain\Repositories\Command\Auth;

use App\Models\User;
use Illuminate\Support\Collection;

interface RolesAndPermissionsRepInterface
{
    public function assignRoles(array $roleRows): void;
    public function givePermissionsByType(User $user, string $belongsTo, string $type = 'model'): void;
    public function removePermissions(array $userIds, array $permissionIds): int;
    public function addPermissions(array $userIds, array $permissionIds): int;
    public function syncRoles(Collection $users, array $rolesToAddIds, array $rolesToRemoveIds): array;
    public function getUsersPermissions(array $userIds): array;
    public function getUsersRoles(array $userIds): array;

}
