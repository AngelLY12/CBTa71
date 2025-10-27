<?php

namespace App\Core\Application\UseCases\Payments\Student\Dashboard;

use App\Core\Domain\Entities\User;
use App\Core\Domain\Repositories\Command\UserRepInterface;
use App\Core\Domain\Repositories\Query\Payments\PaymentConceptQueryRepInterface;

class OverduePaymentsUseCase
{
    public function __construct(
        private PaymentConceptQueryRepInterface $pcqRepo,
        private UserRepInterface $userRepo,
    ) {}
    public function execute(User $user): int {
        $user=$this->userRepo->getUserWithStudentDetail($user);
        return $this->pcqRepo->countOverduePayments($user);
    }
}
