<?php

namespace App\Core\Application\UseCases;

use App\Core\Domain\Entities\User;
use App\Core\Domain\Repositories\Command\StudentDetailReInterface;
use App\Core\Domain\Repositories\Command\UserRepInterface;
use App\Core\Domain\Repositories\Query\UserQueryRepInterface;
use App\Exceptions\NotFound\RoleNotFoundException;
use App\Exceptions\NotFound\StudentsDetailNotFoundException;
use App\Exceptions\NotFound\UserNotFoundException;

class FindUserUseCase
{
    public function __construct(
        private UserQueryRepInterface $uqRepo,
    )
    {
    }
    public function execute(): User
    {
        $user =$this->uqRepo->findAuthUser();
        if(!$user)
        {
            throw new UserNotFoundException();
        }
        return $user;
    }
}
