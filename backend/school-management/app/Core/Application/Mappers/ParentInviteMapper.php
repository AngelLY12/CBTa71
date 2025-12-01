<?php

namespace App\Core\Application\Mappers;

use App\Core\Domain\Entities\ParentInvite;
use Illuminate\Support\Str;

class ParentInviteMapper
{
    public static function toDomain(array $data): ParentInvite
    {
        return new ParentInvite(
            studentId: $data['studentId'],
            email: $data['parentEmail'],
            token: Str::uuid()->toString(),
            expiresAt: now()->addHours(48),
            usedAt: null,
            createdBy: $data['createdBy']
        );
    }
}
