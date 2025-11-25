<?php

namespace App\Core\Domain\Repositories\Query;

interface ParentStudentQueryRepInterface
{
    public function getStudentsOfParent(int $parentId): array;
    public function getParentsOfStudent(int $studentId): array;
    public function exists(int $parentId, int $studentId): bool;
}
