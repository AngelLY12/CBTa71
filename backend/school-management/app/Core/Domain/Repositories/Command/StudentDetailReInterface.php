<?php
namespace App\Core\Domain\Repositories\Command;

use App\Core\Domain\Entities\StudentDetail;
use App\Core\Domain\Entities\User;

interface StudentDetailReInterface
{
    public function findStudentDetails(User $user): StudentDetail;
}
