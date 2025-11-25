<?php

namespace App\Core\Application\UseCases\Admin;

use App\Core\Application\DTO\Request\User\UpdateUserRoleDTO;
use App\Core\Application\DTO\Response\User\UserWithUpdatedRoleResponse;
use App\Core\Domain\Repositories\Command\RolesAndPermissionsRepInterface;
use App\Exceptions\NotFound\UsersNotFoundForUpdateException;

class SyncRoleUseCase
{
    public function __construct(
        private RolesAndPermissionsRepInterface $repo
    )
    {

    }

    public function execute(UpdateUserRoleDTO $dto): UserWithUpdatedRoleResponse
    {
        $updated=$this->repo->updateRoleToMany($dto);
        if (empty($updated))
        {
            throw new UsersNotFoundForUpdateException();
        }
        return $updated;
    }
}
