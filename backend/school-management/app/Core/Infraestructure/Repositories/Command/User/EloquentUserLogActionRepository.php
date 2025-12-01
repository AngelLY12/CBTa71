<?php

namespace App\Core\Infraestructure\Repositories\Command\User;

use App\Core\Domain\Entities\UserActionLog;
use App\Core\Domain\Repositories\Command\User\UserLogActionRepInterface;
use App\Core\Infraestructure\Mappers\UserLogActionMapper;
use App\Models\UserActionLog as ModelsUserActionLog;

class EloquentUserLogActionRepository implements UserLogActionRepInterface
{
    public function create(UserActionLog $log): UserActionLog
    {
        $eloquent=ModelsUserActionLog::create(UserLogActionMapper::toPersistence($log));
        $eloquent->refresh();
        return UserLogActionMapper::toDomain($eloquent);   
    }

    public function deleteOlderLogs(): int
    {
        $count = 0;
        ModelsUserActionLog::where('created_at', '<', now()->subMonths(3))
            ->chunkById(1000, function($logs) use (&$count) {
                $count += $logs->count();
                $logs->each->delete();
            });
        return $count;
    }
}