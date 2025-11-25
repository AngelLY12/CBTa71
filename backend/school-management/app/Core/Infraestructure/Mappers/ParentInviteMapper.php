<?php

namespace App\Core\Infraestructure\Mappers;

use App\Core\Domain\Entities\ParentInvite as EntitiesParentInvite;
use App\Models\ParentInvite;

class ParentInviteMapper
{
    public static function toDomain(ParentInvite $invite): EntitiesParentInvite
    {
        return new EntitiesParentInvite(
            id:$invite->id,
            studentId:$invite->student_id,
            email:$invite->email,
            token:$invite->token,
            expiresAt:$invite->expires_at,
            usedAt:$invite->used_at,
            createdBy:$invite->created_by
        );
    }

    public static function toPersistence(EntitiesParentInvite $invite): array
    {
        return [
            'student_id' => $invite->studentId,
            'email' => $invite->email,
            'token' => $invite->token,
            'expires_at' => $invite->expiresAt,
            'created_by' => $invite->createdBy,
        ];
    }
}
