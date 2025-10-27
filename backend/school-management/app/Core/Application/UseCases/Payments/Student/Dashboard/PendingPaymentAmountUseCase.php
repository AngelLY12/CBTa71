<?php

namespace App\Core\Application\UseCases\Payments\Student\Dashboard;

use App\Core\Application\DTO\Response\PaymentConcept\PendingSummaryResponse;
use App\Core\Domain\Entities\User;
use App\Core\Domain\Repositories\Command\UserRepInterface;
use App\Core\Domain\Repositories\Query\Payments\PaymentConceptQueryRepInterface;

class PendingPaymentAmountUseCase
{
    public function __construct(
        private UserRepInterface $userRepo,
        private PaymentConceptQueryRepInterface $pcqRepo,
    ) {}

    public function execute(User $user): PendingSummaryResponse {
        $user=$this->userRepo->getUserWithStudentDetail($user);
        return $this->pcqRepo->getPendingPaymentConcepts($user);
    }

}
