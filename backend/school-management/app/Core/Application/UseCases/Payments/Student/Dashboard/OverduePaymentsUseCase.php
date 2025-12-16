<?php

namespace App\Core\Application\UseCases\Payments\Student\Dashboard;

use App\Core\Application\DTO\Response\PaymentConcept\PendingSummaryResponse;
use App\Core\Domain\Repositories\Query\Payments\PaymentConceptQueryRepInterface;
use App\Core\Domain\Repositories\Query\User\UserQueryRepInterface;

class OverduePaymentsUseCase
{
    public function __construct(
        private PaymentConceptQueryRepInterface $pcqRepo,
        private UserQueryRepInterface $uqRepo,
    ) {}
    public function execute(int $userId, bool $onlyThisYear): PendingSummaryResponse {
        $user=$this->uqRepo->getUserWithStudentDetail($userId);
        return $this->pcqRepo->getOverduePaymentsSummary($user, $onlyThisYear);
    }
}
