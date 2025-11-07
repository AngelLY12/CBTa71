<?php

namespace App\Core\Application\UseCases\Admin;

use App\Core\Application\DTO\Request\User\UpdateUserRoleDTO;
use App\Core\Application\DTO\Response\User\UserWithUpdatedRoleResponse;
use App\Core\Domain\Repositories\Command\UserRepInterface;

class SyncRoleUseCase
{
    public function __construct(
        private UserRepInterface $userRepo
    )
    {

    }

    public function execute(UpdateUserRoleDTO $dto): UserWithUpdatedRoleResponse
    {
        return $this->userRepo->updateRoleToMany($dto);
    }
}
