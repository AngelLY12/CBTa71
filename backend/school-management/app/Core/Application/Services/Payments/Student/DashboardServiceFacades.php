<?php
namespace App\Core\Application\Services\Payments\Student;

use App\Core\Application\DTO\Response\General\PaginatedResponse;
use App\Core\Application\DTO\Response\Payment\PaymentsSummaryResponse;
use App\Core\Application\DTO\Response\PaymentConcept\PendingSummaryResponse;
use App\Core\Application\Traits\HasCache;
use App\Core\Application\UseCases\Payments\Student\Dashboard\OverduePaymentsUseCase;
use App\Core\Application\UseCases\Payments\Student\Dashboard\PaymentHistoryUseCase;
use App\Core\Application\UseCases\Payments\Student\Dashboard\PaymentsMadeUseCase;
use App\Core\Application\UseCases\Payments\Student\Dashboard\PendingPaymentAmountUseCase;
use App\Core\Domain\Entities\User;
use App\Core\Domain\Enum\Cache\CachePrefix;
use App\Core\Domain\Enum\Cache\StudentCacheSufix;
use App\Core\Infraestructure\Cache\CacheService;

class DashboardServiceFacades {
    use HasCache;
    public function __construct(
        private PendingPaymentAmountUseCase $pending,
        private PaymentsMadeUseCase $payments,
        private OverduePaymentsUseCase $overdue,
        private PaymentHistoryUseCase $history,
        private CacheService $service
    ) {
        $this->setCacheService($service);

    }

    public function pendingPaymentAmount(bool $onlyThisYear, User $user, bool $forceRefresh): PendingSummaryResponse {
        $key = $this->service->makeKey(CachePrefix::STUDENT->value, StudentCacheSufix::DASHBOARD_USER->value . ":pending:$user->id:$onlyThisYear");
        return $this->cache($key,$forceRefresh ,fn() => $this->pending->execute($user->id, $onlyThisYear));
    }

    public function paymentsMade(bool $onlyThisYear, User $user, bool $forceRefresh): PaymentsSummaryResponse {
        $key = $this->service->makeKey(CachePrefix::STUDENT->value, StudentCacheSufix::DASHBOARD_USER->value . ":payments:$user->id:$onlyThisYear");
        return $this->cache($key,$forceRefresh ,fn() => $this->payments->execute($user->id, $onlyThisYear));
    }

    public function overduePayments(bool $onlyThisYear, User $user, bool $forceRefresh): PendingSummaryResponse {
        $key = $this->service->makeKey(CachePrefix::STUDENT->value, StudentCacheSufix::DASHBOARD_USER->value . ":overdue:$user->id:$onlyThisYear");
        return $this->cache($key,$forceRefresh ,fn() => $this->overdue->execute($user->id, $onlyThisYear));
    }

    public function paymentHistory(bool $onlyThisYear, User $user, int $perPage, int $page, bool $forceRefresh): PaginatedResponse {
        $key = $this->service->makeKey(CachePrefix::STUDENT->value, StudentCacheSufix::DASHBOARD_USER->value . ":history:$user->id:$perPage:$page:$onlyThisYear");
        return $this->cache($key,$forceRefresh ,fn() => $this->history->execute($user->id, $perPage, $page, $onlyThisYear));

    }

    public function refreshAll(): void
    {
        $this->service->clearKey(CachePrefix::STUDENT->value, StudentCacheSufix::DASHBOARD_USER->value);
    }
}
