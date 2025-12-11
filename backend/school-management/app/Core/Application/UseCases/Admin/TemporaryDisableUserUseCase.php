<?php

namespace App\Core\Application\UseCases\Admin;

use App\Core\Application\DTO\Response\User\UserChangedStatusResponse;
use App\Core\Domain\Enum\User\UserStatus;
use App\Core\Domain\Repositories\Command\User\UserRepInterface;
use App\Core\Domain\Repositories\Query\User\UserQueryRepInterface;
use App\Core\Domain\Utils\Validators\UserValidator;

class TemporaryDisableUserUseCase
{
    public function __construct(
        private UserRepInterface $userRepo,
        private UserQueryRepInterface $uqRepo,
    ) {}

    public function execute(array $ids): UserChangedStatusResponse
    {
        $users = $this->uqRepo->findByIds($ids);

        foreach ($users as $user) {
            UserValidator::ensureValidStatusTransition($user, UserStatus::BAJA_TEMPORAL);
        }
        return $this->userRepo->changeStatus($ids, UserStatus::BAJA_TEMPORAL->value);
    }
}
