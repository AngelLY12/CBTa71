<?php

namespace App\Core\Application\UseCases\Admin;

use App\Core\Application\DTO\Request\StudentDetail\CreateStudentDetailDTO;
use App\Core\Domain\Entities\User;
use App\Core\Domain\Repositories\Command\StudentDetailReInterface;
use App\Core\Domain\Repositories\Query\UserQueryRepInterface;
use App\Exceptions\Conflict\UserAlreadyHaveStudentDetailException;
use App\Jobs\ClearStaffCacheJob;

class AttachStudentDetailUserCase
{
    public function __construct(
        private UserQueryRepInterface $userRepo,
        private StudentDetailReInterface $sdRepo
    )
    {
    }

    public function execute(CreateStudentDetailDTO $detail):User
    {
        $user=$this->userRepo->findModelEntity($detail->user_id);
        if($user->studentDetail()->exists())
        {
            throw new UserAlreadyHaveStudentDetailException();
        }
        $updatedUser=$this->sdRepo->attachStudentDetail($detail,$user);
        ClearStaffCacheJob::dispatch()
            ->onQueue('cache');
        return $updatedUser;
    }
}
