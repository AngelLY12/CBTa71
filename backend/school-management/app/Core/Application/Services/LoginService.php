<?php
namespace App\Core\Application\Services;

use App\Core\Application\DTO\Request\General\LoginDTO;
use App\Core\Application\DTO\Request\User\CreateUserDTO;
use App\Core\Application\DTO\Response\General\LoginResponse;
use App\Core\Application\UseCases\LoginUseCase;
use App\Core\Application\UseCases\RegisterUseCase;
use App\Core\Domain\Entities\User;

class LoginService{
    public function __construct(
        private LoginUseCase $login,
        private RegisterUseCase $register
    )
    {
   }

   public function login(LoginDTO $request): LoginResponse
   {
        return $this->login->execute($request);
   }

   public function register(CreateUserDTO $user): User
   {
        return $this->register->execute($user);
   }
}
