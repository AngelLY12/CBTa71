<?php

namespace App\Core\Application\UseCases\Admin;

use App\Core\Application\DTO\Request\User\UpdateUserRoleDTO;
use App\Core\Application\DTO\Response\User\UserWithUpdatedRoleResponse;
use App\Core\Domain\Repositories\Command\RolesAndPermissionsRepInterface;

class SyncRoleUseCase
{
    public function __construct(
        private RolesAndPermissionsRepInterface $repo
    )
    {

    }

    public function execute(UpdateUserRoleDTO $dto): UserWithUpdatedRoleResponse
    {
        return $this->repo->updateRoleToMany($dto);
    }
}
