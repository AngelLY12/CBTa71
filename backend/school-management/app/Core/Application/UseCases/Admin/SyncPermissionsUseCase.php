<?php

namespace App\Core\Application\UseCases\Admin;

use App\Core\Application\DTO\Request\User\UpdateUserPermissionsDTO;
use App\Core\Domain\Repositories\Command\RolesAndPermissionsRepInterface;

class SyncPermissionsUseCase
{
    public function __construct(
        private RolesAndPermissionsRepInterface $repo
    )
    {

    }

    public function execute(UpdateUserPermissionsDTO $dto): array
    {
        return $this->repo->updatePermissionToMany($dto);
    }
}
