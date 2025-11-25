<?php

namespace App\Core\Application\Mappers;

use App\Core\Domain\Entities\ParentStudent;
use App\Core\Domain\Enum\User\RelationshipType;

class ParentStudentMapper
{
    public static function toDomain(array $data): ParentStudent
    {
        return new ParentStudent(
            parentId:$data['parentId'],
            studentId:$data['studentId'],
            parentRoleId:$data['parentRoleId'],
            studentRoleId:$data['studentRoleId'],
            relationship: isset($data['relationship'])
            ? RelationshipType::from($data['relationship'])
            : null
        );
    }
}
