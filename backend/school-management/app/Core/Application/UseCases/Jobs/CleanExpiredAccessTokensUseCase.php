<?php

namespace App\Core\Application\UseCases\Jobs;

use App\Core\Domain\Repositories\Command\UserRepInterface;

class CleanExpiredAccessTokensUseCase
{
    public function __construct(
    private UserRepInterface $userRepo)
    {
    }
    public function execute():int
    {
        $sanctum=$this->userRepo->deletionInvalidTokens();
        return $sanctum;
    }
}
