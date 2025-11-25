<?php

namespace App\Core\Domain\Entities;

use App\Core\Domain\Enum\User\RelationshipType;

/**
 * @OA\Schema(
 *     schema="ParentStudent",
 *     type="object",
 *     required={"parentId","studentId","parentRoleId","studentRoleId"},
 *     @OA\Property(property="parentId", type="integer"),
 *     @OA\Property(property="studentId", type="integer"),
 *     @OA\Property(property="parentRoleId", type="integer"),
 *     @OA\Property(property="studentRoleId", type="integer"),
 *     @OA\Property(property="relationship", ref="#/components/schemas/RelationshipType", nullable=true)
 * )
 */
class ParentStudent
{
    public function __construct(
        public readonly int $parentId,
        public readonly int $studentId,
        public readonly int $parentRoleId,
        public readonly int $studentRoleId,
        public readonly ?RelationshipType $relationship,
    ) {}
}
