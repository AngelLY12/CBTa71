<?php

namespace App\Core\Application\UseCases;

use App\Core\Application\Mappers\GeneralMapper;
use App\Core\Domain\Repositories\Command\RefreshTokenRepInterface;
use App\Core\Domain\Repositories\Command\UserRepInterface;
use App\Core\Domain\Utils\Validators\TokenValidator;

class RefreshTokenUseCase
{
    public function __construct(
        private RefreshTokenRepInterface $refresh,
        private UserRepInterface $userRepo,
    )
    {
    }

    public function execute(string $refreshTokenValue)
    {
        $refreshToken= $this->refresh->findByToken($refreshTokenValue);
        TokenValidator::ensureIsTokenValid($refreshToken);
        $user = $this->userRepo->findById($refreshToken->user_id);
        $this->refresh->revokeRefreshToken($refreshTokenValue);
        $newAccessToken  = $this->userRepo->createToken($user, 'api-token');
        $newRefreshToken = $this->userRepo->createRefreshToken($user, 'refresh-token');
        return GeneralMapper::toLoginResponse($newAccessToken,
        $newRefreshToken,
        'Bearer');

    }
}
