<?php

namespace App\Core\Domain\Repositories\Query\User;

use App\Core\Application\DTO\Response\Parents\ParentChildrenResponse;

interface ParentStudentQueryRepInterface
{
    public function getStudentsOfParent(int $parentId): ?ParentChildrenResponse;
    public function getParentsOfStudent(int $studentId): array;
    public function exists(int $parentId, int $studentId): bool;
}
