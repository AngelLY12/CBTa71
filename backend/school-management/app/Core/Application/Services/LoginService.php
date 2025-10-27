<?php
namespace App\Core\Application\Services;

use App\Core\Application\DTO\Request\General\LoginDTO;
use App\Core\Application\DTO\Response\General\LoginResponse;
use App\Core\Application\UseCases\LoginUseCase;

class LoginService{
    public function __construct(
        private LoginUseCase $login
    )
    {
   }

   public function login(LoginDTO $request): LoginResponse
   {
        return $this->login->execute($request);
   }
}
