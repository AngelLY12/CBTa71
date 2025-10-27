<?php

namespace App\Core\Application\UseCases\Payments\Student\PendingPayment;

use App\Core\Domain\Entities\User;
use App\Core\Domain\Repositories\Command\UserRepInterface;
use App\Core\Domain\Repositories\Query\Payments\PaymentConceptQueryRepInterface;
use App\Exceptions\UserInactiveException;

class ShowPendingPaymentsUseCase
{

    public function __construct(
        private PaymentConceptQueryRepInterface $pcqRepo,
        private UserRepInterface $userRepo
    ) {}

    public function execute(User $user): array {
        if (!$user->isActive()) {
            throw new UserInactiveException();
        }
        $user=$this->userRepo->getUserWithStudentDetail($user);
        $pendingArray=$this->pcqRepo->getPendingPaymentConceptsWithDetails($user);
        return $pendingArray;
    }
}
