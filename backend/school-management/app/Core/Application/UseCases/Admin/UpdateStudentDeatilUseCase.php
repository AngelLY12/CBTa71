<?php

namespace App\Core\Application\UseCases\Admin;

use App\Core\Domain\Entities\User;
use App\Core\Domain\Repositories\Command\User\StudentDetailReInterface;

class UpdateStudentDeatilUseCase
{
    public function __construct(
        private StudentDetailReInterface $repo
    )
    {
    }

    public function execute(int $userId, array $fields): User
    {
        return $this->repo->updateStudentDetails($userId, $fields);
    }
}
