<?php

namespace App\Core\Application\UseCases;

use App\Core\Domain\Entities\User;
use App\Core\Domain\Repositories\Command\UserRepInterface;

class UpdateUserUseCase
{
    public function __construct(private UserRepInterface $userRepo)
    {
    }

    public function execute(int $userId, array $fields)
    {
        return $this->userRepo->update($userId, $fields);
    }
}
