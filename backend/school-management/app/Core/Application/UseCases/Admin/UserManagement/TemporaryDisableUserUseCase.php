<?php

namespace App\Core\Application\UseCases\Admin;

use App\Core\Application\DTO\Response\User\UserChangedStatusResponse;
use App\Core\Domain\Enum\User\UserStatus;
use App\Core\Domain\Repositories\Command\User\UserRepInterface;
use App\Core\Domain\Repositories\Query\User\UserQueryRepInterface;
use App\Core\Domain\Utils\Validators\UserValidator;
use App\Jobs\ClearStaffCacheJob;

class TemporaryDisableUserUseCase extends BaseChangeUserStatusUseCase
{
    protected function getTargetStatus(): UserStatus
    {
        return UserStatus::BAJA_TEMPORAL;
    }

    protected function validateUsers(iterable $users): void
    {
        foreach ($users as $user) {
            UserValidator::ensureValidStatusTransition(
                $user,
                UserStatus::BAJA_TEMPORAL
            );
        }
    }

}
