<?php

namespace App\Core\Application\UseCases\Payments\Student\PendingPayment;

use App\Core\Domain\Repositories\Query\Payments\PaymentConceptQueryRepInterface;
use App\Core\Domain\Repositories\Query\User\UserQueryRepInterface;

class ShowOverduePaymentsUseCase
{
    public function __construct(
        private PaymentConceptQueryRepInterface $pcqRepo,
        private UserQueryRepInterface $userRepo
    ) {}
    public function execute(int $userId): array {
        $user=$this->userRepo->getUserWithStudentDetail($userId);
        $overdueArray=$this->pcqRepo->getOverduePayments($user);
        return $overdueArray;
    }

}
