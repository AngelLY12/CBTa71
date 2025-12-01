<?php

namespace App\Core\Application\Mappers;

use App\Core\Domain\Entities\UserActionLog;
use App\Models\User;
use Illuminate\Http\Request;

class UserActionLogMapper
{
    public static function toDomain(User $user, Request $request): UserActionLog
    {
        return new UserActionLog(
            userId:$user?->id,
            roles:$user?->roles->pluck('name')->toArray() ?? null,
            ip:$request->ip(),
            method:$request->method(),
            url:$request->fullUrl(),
        );
    }
}