<?php

namespace App\Core\Domain\Repositories\Command\Auth;

use App\Models\User;
use Illuminate\Support\Collection;

interface RolesAndPermissionsRepInterface
{
    public function assignRoles(array $roleRows): void;
    public function givePermissionsByType(User $user, string $belongsTo, string $type = 'model'): void;
    public function removePermissions(array $userIds, array $permissionIds): void;
    public function addPermissions(array $userIds, array $permissionIds): void;
    public function syncRoles(Collection $users, array $rolesToAddIds, array $rolesToRemoveIds): void;

}
