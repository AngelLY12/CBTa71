<?php

namespace App\Core\Application\UseCases\Jobs;

use App\Core\Domain\Repositories\Command\UserRepInterface;

class CleanDeletedUsersUseCase
{
    public function __construct(
        private UserRepInterface $userRepo
    )
    {
    }

    public function execute():int
    {
        return $this->userRepo->deletionEliminateUsers();
    }
}
