<?php
namespace App\Core\Domain\Repositories\Command\User;

use App\Core\Application\DTO\Request\StudentDetail\CreateStudentDetailDTO;
use App\Core\Domain\Entities\StudentDetail;
use App\Core\Domain\Entities\User;
use App\Models\User as ModelsUser;

interface StudentDetailReInterface
{
    public function findStudentDetails(int $userId): ?StudentDetail;
    public function incrementSemesterForAll(): int;
    public function getStudentsExceedingSemesterLimit(int $maxSemester = 12): iterable;
    public function updateStudentDetails(int $user_id, array $field): User;
    public function insertStudentDetails(array $studentDetails): void;
    public function attachStudentDetail(CreateStudentDetailDTO $detail, ModelsUser $user): User;
}
