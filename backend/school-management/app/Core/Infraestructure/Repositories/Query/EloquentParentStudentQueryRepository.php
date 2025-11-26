<?php

namespace App\Core\Infraestructure\Repositories\Query;

use App\Core\Application\DTO\Response\Parents\ParentChildrenResponse;
use App\Core\Application\Mappers\ParentStudentMapper as MappersParentStudentMapper;
use App\Core\Domain\Repositories\Query\ParentStudentQueryRepInterface;
use App\Core\Infraestructure\Mappers\ParentStudentMapper;
use App\Models\ParentStudent as EloquentParentStudent;


class EloquentParentStudentQueryRepository implements ParentStudentQueryRepInterface
{
    public function getStudentsOfParent(int $parentId): ?ParentChildrenResponse
    {
         $relations = EloquentParentStudent::with([
            'parent:id,name,last_name',
            'student:id,name,last_name'
        ])
        ->where('parent_id', $parentId)
        ->get();
        if($relations->isEmpty())
        {
            return null;
        }
        $parent = $relations->first()->parent;

        $childrenData = $relations->map(fn($relation) => [
            'id' => $relation->student->id,
            'fullName' => "{$relation->student->name} {$relation->student->last_name}"
        ])->toArray();

        return MappersParentStudentMapper::toParentChildrenResponse([
            'parentId' => $parent->id,
            'parentName' => "{$parent->name} {$parent->last_name}",
            'childrenData' => $childrenData,
        ]);
    }
    public function getParentsOfStudent(int $studentId): array
    {
        return EloquentParentStudent::with('users:id,name,last_name')
        ->where('student_id', $studentId)
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
