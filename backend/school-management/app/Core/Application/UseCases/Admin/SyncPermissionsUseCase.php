<?php

namespace App\Core\Application\UseCases\Admin;

use App\Core\Application\DTO\Request\User\UpdateUserPermissionsDTO;
use App\Core\Domain\Repositories\Command\UserRepInterface;
use App\Core\Domain\Repositories\Query\UserQueryRepInterface;

class SyncPermissionsUseCase
{
    public function __construct(
        private UserRepInterface $uqRepo
    )
    {

    }

    public function execute(UpdateUserPermissionsDTO $dto): void
    {
        $this->uqRepo->updatePermissionToMany($dto);
    }
}
