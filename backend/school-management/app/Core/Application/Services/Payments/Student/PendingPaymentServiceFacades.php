<?php

namespace App\Core\Application\Services\Payments\Student;

use App\Core\Application\UseCases\Payments\Student\PendingPayment\PayConceptUseCase;
use App\Core\Application\UseCases\Payments\Student\PendingPayment\ShowOverduePaymentsUseCase;
use App\Core\Application\UseCases\Payments\Student\PendingPayment\ShowPendingPaymentsUseCase;
use App\Core\Domain\Entities\User;

class PendingPaymentServiceFacades{

    public function __construct(
        private ShowPendingPaymentsUseCase $pending,
        private ShowOverduePaymentsUseCase $overdue,
        private PayConceptUseCase $pay

    ) {}

    public function showPendingPayments(User $user): array {
        return $this->pending->execute($user);
    }

    public function showOverduePayments(User $user): array {
        return $this->overdue->execute($user);
    }

    public function payConcept(User $user, int $conceptId): string {
        return $this->pay->execute($user,$conceptId);
    }

}
