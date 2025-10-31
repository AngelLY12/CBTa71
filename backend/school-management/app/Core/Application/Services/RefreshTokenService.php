<?php

namespace App\Core\Application\Services;

use App\Core\Application\DTO\Response\General\LoginResponse;
use App\Core\Application\UseCases\LogoutUseCase;
use App\Core\Application\UseCases\RefreshTokenUseCase;
use App\Models\User;

class RefreshTokenService
{
    public function __construct(
        private RefreshTokenUseCase $refresh,
        private LogoutUseCase $logout
    )
    {
    }
    public function refreshToken(string $refreshToken): LoginResponse
    {
        return $this->refresh->execute($refreshToken);
    }

    public function logout(User $user, string $refreshToken)
    {
        $this->logout->execute($user,$refreshToken);
    }

}
