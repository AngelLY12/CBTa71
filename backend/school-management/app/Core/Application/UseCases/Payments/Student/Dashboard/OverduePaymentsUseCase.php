<?php

namespace App\Core\Application\UseCases\Payments\Student\Dashboard;

use App\Core\Domain\Repositories\Query\Payments\PaymentConceptQueryRepInterface;
use App\Core\Domain\Repositories\Query\UserQueryRepInterface;

class OverduePaymentsUseCase
{
    public function __construct(
        private PaymentConceptQueryRepInterface $pcqRepo,
        private UserQueryRepInterface $uqRepo,
    ) {}
    public function execute(int $userId): int {
        $user=$this->uqRepo->getUserWithStudentDetail($userId);
        return $this->pcqRepo->countOverduePayments($user);
    }
}
