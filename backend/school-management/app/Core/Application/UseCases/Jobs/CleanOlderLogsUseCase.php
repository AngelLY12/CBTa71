<?php

namespace App\Core\Application\UseCases\Jobs;

use App\Core\Domain\Repositories\Command\User\UserLogActionRepInterface;

class CleanOlderLogsUseCase
{
    public function __construct(private UserLogActionRepInterface $logs)
    {
    }

    public function execute(): int
    {
        return $this->logs->deleteOlderLogs();
    }
}