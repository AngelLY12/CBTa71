<?php

namespace App\Core\Application\UseCases\Admin;

use App\Core\Application\DTO\Request\StudentDetail\CreateStudentDetailDTO;
use App\Core\Domain\Entities\User;
use App\Core\Domain\Repositories\Command\UserRepInterface;
use App\Jobs\ClearStaffCacheJob;
use App\Jobs\ClearStudentCacheJob;

class AttachStudentDetailUserCase
{
    public function __construct(
        private UserRepInterface $userRepo
    )
    {
    }

    public function execute(CreateStudentDetailDTO $detail):User
    {
        $user=$this->userRepo->attachStudentDetail($detail);
        ClearStaffCacheJob::dispatch()->delay(now()->addSeconds(rand(1, 10)));
        return $user;
    }
}
