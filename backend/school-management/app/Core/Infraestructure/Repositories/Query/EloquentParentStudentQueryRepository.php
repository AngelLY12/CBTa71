<?php

namespace App\Core\Infraestructure\Repositories\Query;

use App\Core\Domain\Repositories\Query\ParentStudentQueryRepInterface;
use App\Core\Infraestructure\Mappers\ParentStudentMapper;
use App\Models\ParentStudent as EloquentParentStudent;


class EloquentParentStudentQueryRepository implements ParentStudentQueryRepInterface
{
    public function getStudentsOfParent(int $parentId): array
    {
        return EloquentParentStudent::where('parent_id', $parentId)
        ->get()
        ->map(fn($relation) => ParentStudentMapper::toDomain($relation))
        ->toArray();
    }
    public function getParentsOfStudent(int $studentId): array
    {
        return EloquentParentStudent::where('student_id', $studentId)
        ->get()
        ->map(fn($relation) => ParentStudentMapper::toDomain($relation))
        ->toArray();
    }
    public function exists(int $parentId, int $studentId): bool
    {
        return EloquentParentStudent::where('parent_id', $parentId)
            ->where('student_id', $studentId)
            ->exists();
    }
}
