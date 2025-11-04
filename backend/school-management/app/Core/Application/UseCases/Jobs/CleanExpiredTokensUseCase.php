<?php

namespace App\Core\Application\UseCases\Jobs;

use App\Core\Domain\Repositories\Command\RefreshTokenRepInterface;
use App\Core\Domain\Repositories\Command\UserRepInterface;

class CleanExpiredTokensUseCase
{
    public function __construct(private RefreshTokenRepInterface $rtRepo,
    private UserRepInterface $userRepo)
    {
    }
    public function execute():array
    {
        $refresh=$this->rtRepo->deletionInvalidTokens();
        $sanctum=$this->userRepo->deletionInvalidTokens();
        return [
            'sanctum' => $sanctum,
            'refresh' => $refresh
        ];
    }
}
