<?php

namespace App\Core\Application\UseCases\Misc;

use App\Core\Application\Mappers\UserActionLogMapper;
use App\Core\Domain\Entities\UserActionLog;
use App\Core\Domain\Repositories\Command\User\UserLogActionRepInterface;
use App\Models\User;
use Illuminate\Http\Request;

class CreateUserActionLogUseCase
{
    public function __construct(
        private UserLogActionRepInterface $ulaRepo
    )
    {
    }

    public function execute(User $user, array $request): UserActionLog
    {
        $log=UserActionLogMapper::toDomain($user,$request);
        return $this->ulaRepo->create($log);
    }
}
