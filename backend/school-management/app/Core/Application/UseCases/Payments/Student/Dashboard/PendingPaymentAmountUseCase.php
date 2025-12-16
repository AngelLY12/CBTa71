<?php

namespace App\Core\Application\UseCases\Payments\Student\Dashboard;

use App\Core\Application\DTO\Response\PaymentConcept\PendingSummaryResponse;
use App\Core\Domain\Repositories\Query\Payments\PaymentConceptQueryRepInterface;
use App\Core\Domain\Repositories\Query\User\UserQueryRepInterface;

class PendingPaymentAmountUseCase
{
    public function __construct(
        private UserQueryRepInterface $uqRepo,
        private PaymentConceptQueryRepInterface $pcqRepo,
    ) {}

    public function execute(int $userId, bool $onlyThisYear): PendingSummaryResponse {
        $user=$this->uqRepo->getUserWithStudentDetail($userId);
        return $this->pcqRepo->getPendingPaymentConcepts($user, $onlyThisYear);
    }

}
