<?php

namespace App\Core\Application\UseCases\Payments\Student\PendingPayment;

use App\Core\Domain\Entities\User;
use App\Core\Domain\Repositories\Command\UserRepInterface;
use App\Core\Domain\Repositories\Query\Payments\PaymentConceptQueryRepInterface;

class ShowOverduePaymentsUseCase
{
    public function __construct(
        private PaymentConceptQueryRepInterface $pcqRepo,
        private UserRepInterface $userRepo
    ) {}
    public function execute(User $user): array {
        $user=$this->userRepo->getUserWithStudentDetail($user);
        $overdueArray=$this->pcqRepo->getOverduePayments($user);
        return $overdueArray;
    }

}
