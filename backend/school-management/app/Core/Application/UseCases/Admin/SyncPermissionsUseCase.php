<?php

namespace App\Core\Application\UseCases\Admin;

use App\Core\Application\DTO\Request\User\UpdateUserPermissionsDTO;
use App\Core\Domain\Repositories\Command\RolesAndPermissionsRepInterface;
use App\Exceptions\NotFound\UsersNotFoundForUpdateException;

class SyncPermissionsUseCase
{
    public function __construct(
        private RolesAndPermissionsRepInterface $repo
    )
    {

    }

    public function execute(UpdateUserPermissionsDTO $dto): array
    {
        $updated=$this->repo->updatePermissionToMany($dto);
        if (empty($updated))
        {
            throw new UsersNotFoundForUpdateException();
        }
        return $updated;
    }
}
