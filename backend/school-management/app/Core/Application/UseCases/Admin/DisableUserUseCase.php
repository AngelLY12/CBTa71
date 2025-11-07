<?php

namespace App\Core\Application\UseCases\Admin;

use App\Core\Application\DTO\Response\User\UserChangedStatusResponse;
use App\Core\Domain\Repositories\Command\UserRepInterface;
use App\Core\Domain\Repositories\Query\UserQueryRepInterface;
use App\Core\Domain\Utils\Validators\UserValidator;

class DisableUserUseCase
{
    public function __construct(
        private UserRepInterface $userRepo,
        private UserQueryRepInterface $uqRepo,
    )
    {
    }

    public function execute(array $ids): UserChangedStatusResponse
    {
        $users = $this->uqRepo->findByIds($ids);
        foreach($users as $user){
            UserValidator::ensureValidStatusTransition($user, 'baja');
        }
        return $this->userRepo->changeStatus($ids, 'baja');
    }
}
