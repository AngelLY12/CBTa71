<?php

namespace App\Core\Domain\Repositories\Command;

use App\Core\Application\DTO\Request\User\UpdateUserPermissionsDTO;
use App\Core\Application\DTO\Request\User\UpdateUserRoleDTO;
use App\Core\Application\DTO\Response\User\UserWithUpdatedRoleResponse;

interface RolesAndPermissionsRepInterface
{
    public function updatePermissionToMany(UpdateUserPermissionsDTO $dto): array;
    public function updateRoleToMany(UpdateUserRoleDTO $dto): UserWithUpdatedRoleResponse;
}
