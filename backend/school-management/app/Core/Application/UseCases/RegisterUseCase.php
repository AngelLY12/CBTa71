<?php

namespace App\Core\Application\UseCases;

use App\Core\Application\DTO\Request\User\CreateUserDTO;
use App\Core\Application\Mappers\UserMapper;
use App\Core\Domain\Entities\User;
use App\Core\Domain\Repositories\Command\UserRepInterface;
use App\Core\Domain\Utils\Validators\UserValidator;
use Illuminate\Support\Facades\DB;

class RegisterUseCase
{
    public function __construct(
        private UserRepInterface $userRepo
    )
    {}

    public function execute(CreateUserDTO $create): User
    {
        UserValidator::ensureUserDataIsValid($create);
        return DB::transaction(function () use ($create) {
            return $this->userRepo->create($create);
        });
    }
}
