<?php
namespace App\Core\Application\Services\Payments\Student;

use App\Core\Application\DTO\Response\General\DashboardDataUserResponse;
use App\Core\Application\DTO\Response\General\PaginatedResponse;
use App\Core\Application\DTO\Response\PaymentConcept\PendingSummaryResponse;
use App\Core\Application\Mappers\GeneralMapper;
use App\Core\Application\Traits\HasCache;
use App\Core\Application\UseCases\Payments\Student\Dashboard\OverduePaymentsUseCase;
use App\Core\Application\UseCases\Payments\Student\Dashboard\PaymentHistoryUseCase;
use App\Core\Application\UseCases\Payments\Student\Dashboard\PaymentsMadeUseCase;
use App\Core\Application\UseCases\Payments\Student\Dashboard\PendingPaymentAmountUseCase;
use App\Core\Domain\Entities\User;
use App\Core\Infraestructure\Cache\CacheService;

class DashboardServiceFacades {
    use HasCache;
    private string $prefix= 'dashboard-user';
    public function __construct(
        private PendingPaymentAmountUseCase $pending,
        private PaymentsMadeUseCase $payments,
        private OverduePaymentsUseCase $overdue,
        private PaymentHistoryUseCase $history,
        private CacheService $service
    ) {}

    public function pendingPaymentAmount(User $user, bool $forceRefresh): PendingSummaryResponse {
        $key = "$this->prefix:pending:$user->id";
        return $this->cache($key,$forceRefresh ,fn() => $this->pending->execute($user->id));
    }

    public function paymentsMade(User $user, bool $forceRefresh): string {
        $key = "$this->prefix:payments:$user->id";
        return $this->cache($key,$forceRefresh ,fn() => $this->payments->execute($user->id));
    }

    public function overduePayments(User $user, bool $forceRefresh): int {
        $key = "$this->prefix:overdue:$user->id";
        return $this->cache($key,$forceRefresh ,fn() => $this->overdue->execute($user->id));
    }

    public function paymentHistory(User $user, int $perPage, int $page, bool $forceRefresh): PaginatedResponse {
        $key = "$this->prefix:history:$user->id:$perPage:$page";
        return $this->cache($key,$forceRefresh ,fn() => $this->history->execute($user->id, $perPage, $page));

    }
    public function getDashboardData(User $user, bool $forceRefresh): DashboardDataUserResponse {
        return GeneralMapper::toDashboardDataUserResponse(
            $this->paymentsMade($user, $forceRefresh),
            $this->pendingPaymentAmount($user, $forceRefresh),
            $this->overduePayments($user, $forceRefresh));
    }

    public function refreshAll(): void
    {
        $this->service->clearPrefix($this->prefix);
    }
}
