<?php

namespace App\Core\Domain\Repositories\Command\User;

use App\Core\Domain\Entities\UserActionLog;

interface UserLogActionRepInterface
{
    public function create(UserActionLog $log): UserActionLog;
    public function deleteOlderLogs(): int;
}