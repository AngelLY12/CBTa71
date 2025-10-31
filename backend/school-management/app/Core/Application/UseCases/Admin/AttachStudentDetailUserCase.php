<?php

namespace App\Core\Application\UseCases\Admin;

use App\Core\Application\DTO\Request\StudentDetail\CreateStudentDetailDTO;
use App\Core\Domain\Entities\User;
use App\Core\Domain\Repositories\Command\UserRepInterface;

class AttachStudentDetailUserCase
{
    public function __construct(
        private UserRepInterface $userRepo
    )
    {
    }

    public function execute(CreateStudentDetailDTO $detail):User
    {
        return $this->userRepo->attachStudentDetail($detail);
    }
}
