<?php

namespace App\Core\Application\UseCases\Payments\Staff\Dashboard;


use App\Core\Domain\Repositories\Query\User\UserQueryRepInterface;

class GetAllStudentsUseCase{
    public function __construct(
        private UserQueryRepInterface $uqRepo,
    )
    {
    }

    public function execute(bool $onlyThisYear):int
    {
        return $this->uqRepo->countStudents($onlyThisYear);
    }
}
