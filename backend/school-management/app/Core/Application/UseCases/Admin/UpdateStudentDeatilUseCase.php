<?php

namespace App\Core\Application\UseCases\Admin;

use App\Core\Domain\Entities\User;
use App\Core\Domain\Repositories\Command\User\StudentDetailReInterface;
use App\Jobs\ClearStaffCacheJob;

class UpdateStudentDeatilUseCase
{
    public function __construct(
        private StudentDetailReInterface $repo
    )
    {
    }

    public function execute(int $userId, array $fields): User
    {
        $update=$this->repo->updateStudentDetails($userId, $fields);
        ClearStaffCacheJob::dispatch()->delay(now()->addSeconds(rand(1, 10)));
        return $update;
    }
}
