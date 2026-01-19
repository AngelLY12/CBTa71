<?php

namespace App\Core\Application\Mappers;

use App\Core\Domain\Entities\UserActionLog;
use App\Models\User;
use Illuminate\Http\Request;

class UserActionLogMapper
{
    public static function toDomain(User $user, array $request): UserActionLog
    {
        return new UserActionLog(
            method: $request['method'],
            url: $request['url'],
            roles: $user?->roles->pluck('name')->toArray() ?? null,
            userId: $user?->id,
            ip: $request['ip'],
        );
    }
}
