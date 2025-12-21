<?php

namespace App\Core\Application\UseCases\User;

use App\Core\Application\Traits\HasCache;
use App\Core\Domain\Entities\User;
use App\Core\Domain\Enum\Cache\CachePrefix;
use App\Core\Domain\Repositories\Query\User\UserQueryRepInterface;
use App\Core\Infraestructure\Cache\CacheService;
use App\Exceptions\NotFound\UserNotFoundException;

class FindUserUseCase
{
    use HasCache;

    public function __construct(
        private UserQueryRepInterface $uqRepo,
        CacheService $service
    )
    {
        $this->setCacheService($service);
    }
    public function execute(bool $forceRefresh): User
    {

        $user =$this->uqRepo->findAuthUser();
        if(!$user)
        {
            throw new UserNotFoundException();
        }
        $key = CachePrefix::USER->value . ":$user->id";
        return $this->cache($key, $forceRefresh, fn() => $user);
    }
}
