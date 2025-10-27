<?php
namespace App\Core\Application\Services\Payments\Student;

use App\Core\Application\DTO\Response\General\DashboardDataUserResponse;
use App\Core\Application\DTO\Response\PaymentConcept\PendingSummaryResponse;
use App\Core\Application\Mappers\GeneralMapper;
use App\Core\Application\UseCases\Payments\Student\Dashboard\OverduePaymentsUseCase;
use App\Core\Application\UseCases\Payments\Student\Dashboard\PaymentHistoryUseCase;
use App\Core\Application\UseCases\Payments\Student\Dashboard\PaymentsMadeUseCase;
use App\Core\Application\UseCases\Payments\Student\Dashboard\PendingPaymentAmountUseCase;
use App\Core\Domain\Entities\User;


class DashboardServiceFacades {

    public function __construct(
        private PendingPaymentAmountUseCase $pending,
        private PaymentsMadeUseCase $payments,
        private OverduePaymentsUseCase $overdue,
        private PaymentHistoryUseCase $history
    ) {}

    public function pendingPaymentAmount(User $user): PendingSummaryResponse {
        return $this->pending->execute($user);
    }

    public function paymentsMade(User $user): int {
        return $this->payments->execute($user);
    }

    public function overduePayments(User $user): int {
        return $this->overdue->execute($user);
    }

    public function paymentHistory(User $user): array {
        return $this->history->execute($user);

    }
    public function getDashboardData(User $user): DashboardDataUserResponse {
        return GeneralMapper::toDashboardDataUserResponse(
            $this->paymentsMade($user),
            $this->pendingPaymentAmount($user),
            $this->overduePayments($user));
    }
}
