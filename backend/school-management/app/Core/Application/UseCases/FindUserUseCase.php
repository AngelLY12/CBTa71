<?php

namespace App\Core\Application\UseCases;

use App\Core\Application\Traits\HasCache;
use App\Core\Domain\Entities\User;
use App\Core\Domain\Repositories\Query\UserQueryRepInterface;
use App\Exceptions\NotFound\UserNotFoundException;

class FindUserUseCase
{
    use HasCache;

    private string $prefix = 'user';
    public function __construct(
        private UserQueryRepInterface $uqRepo,
    )
    {
    }
    public function execute(bool $forceRefresh): User
    {

        $user =$this->uqRepo->findAuthUser();
        if(!$user)
        {
            throw new UserNotFoundException();
        }
        $key = "$this->prefix:$user->id";
        return $this->cache($key, $forceRefresh, fn() => $user);
    }
}
