<?php

namespace App\Core\Application\UseCases\Auth;

use App\Core\Application\Mappers\GeneralMapper;
use App\Core\Domain\Repositories\Command\Auth\RefreshTokenRepInterface;
use App\Core\Domain\Repositories\Command\User\UserRepInterface;
use App\Core\Domain\Repositories\Query\User\UserQueryRepInterface;
use App\Core\Domain\Utils\Validators\TokenValidator;
use App\Core\Domain\Utils\Validators\UserValidator;

class RefreshTokenUseCase
{
    public function __construct(
        private RefreshTokenRepInterface $refresh,
        private UserRepInterface $userRepo,
        private UserQueryRepInterface $uqRepo
    )
    {
    }

    public function execute(string $refreshTokenValue)
    {
        $refreshToken= $this->refresh->findByToken($refreshTokenValue);
        TokenValidator::ensureIsTokenValid($refreshToken);
        $user = $this->uqRepo->findById($refreshToken->user_id);
        UserValidator::ensureUserIsActive($user);
        $this->refresh->revokeRefreshToken($refreshTokenValue);
        $userRoles= $user->getRoleNames();
        $userData=$this->formatUserData($userRoles, $user->fullName(), $user->id);
        $newAccessToken  = $this->userRepo->createToken($user->id, 'api-token');
        $newRefreshToken = $this->userRepo->createRefreshToken($user->id, 'refresh-token');
        return GeneralMapper::toLoginResponse($newAccessToken,
        $newRefreshToken,
        'Bearer',
        $userData);
    }

    private function formatUserData(array $roles, string $fullName, int $id): array
   {
        return [
            'id' => $id,
            'fullName' => $fullName,
            'roles' => $roles
        ];
   }
}
