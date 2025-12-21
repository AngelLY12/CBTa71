<?php

namespace App\Core\Application\UseCases\Payments\Student\PendingPayment;

use App\Core\Domain\Repositories\Query\Payments\PaymentConceptQueryRepInterface;
use App\Core\Domain\Repositories\Query\User\UserQueryRepInterface;
use App\Exceptions\Unauthorized\UserInactiveException;

class ShowPendingPaymentsUseCase
{

    public function __construct(
        private PaymentConceptQueryRepInterface $pcqRepo,
        private UserQueryRepInterface $userRepo
    ) {}

    public function execute(int $userId): array {
        $user=$this->userRepo->getUserWithStudentDetail($userId);
        if (!$user->isActive()) {
            throw new UserInactiveException();
        }
        $pendingArray=$this->pcqRepo->getPendingPaymentConceptsWithDetails($user);
        return $pendingArray;
    }
}
