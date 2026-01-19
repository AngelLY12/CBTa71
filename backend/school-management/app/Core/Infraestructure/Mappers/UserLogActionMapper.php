<?php

namespace App\Core\Infraestructure\Mappers;

use App\Core\Domain\Entities\UserActionLog as EntitiesUserActionLog;
use App\Models\UserActionLog;

class UserLogActionMapper
{
    public static function toDomain(UserActionLog $log): EntitiesUserActionLog
    {
        return new EntitiesUserActionLog(
            method: $log->method,
            url: $log->url,
            roles: $log->roles ?? null,
            id: $log->id,
            userId: $log->user_id ?? null,
            ip: $log->ip ?? null,
        );
    }

    public static function toPersistence(EntitiesUserActionLog $log): array
    {
        return [
            'user_id' => $log->userId ?? null,
            'roles' => $log->roles ?? null,
            'ip' => $log->ip ?? null,
            'method' => $log->method,
            'url' => $log->url,
        ];

    }
}
