<?php

namespace App\Core\Application\Services\Payments\Staff;

use App\Core\Application\DTO\Response\General\PaginatedResponse;
use App\Core\Application\DTO\Response\General\StripePayoutResponse;
use App\Core\Application\DTO\Response\Payment\FinancialSummaryResponse;
use App\Core\Application\DTO\Response\PaymentConcept\PendingSummaryResponse;
use App\Core\Application\Traits\HasCache;
use App\Core\Application\UseCases\Payments\Staff\Dashboard\CreatePayoutUseCase;
use App\Core\Application\UseCases\Payments\Staff\Dashboard\GetAllConceptsUseCase;
use App\Core\Application\UseCases\Payments\Staff\Dashboard\GetAllStudentsUseCase;
use App\Core\Application\UseCases\Payments\Staff\Dashboard\PaymentsMadeUseCase;
use App\Core\Application\UseCases\Payments\Staff\Dashboard\PendingPaymentAmountUseCase;
use App\Core\Domain\Enum\Cache\CachePrefix;
use App\Core\Domain\Enum\Cache\StaffCacheSufix;
use App\Core\Infraestructure\Cache\CacheService;

class DashboardServiceFacades{
    use HasCache;
    public function __construct(
        private PendingPaymentAmountUseCase $pending,
        private GetAllStudentsUseCase $students,
        private PaymentsMadeUseCase $payments,
        private GetAllConceptsUseCase $concepts,
        private CreatePayoutUseCase $payout,
        private CacheService $service
    )
    {
        $this->setCacheService($service);
    }

    public function pendingPaymentAmount(bool $onlyThisYear, bool $forceRefresh): PendingSummaryResponse
    {
        $key = $this->service->makeKey(CachePrefix::STAFF->value, StaffCacheSufix::DASHBOARD->value . ":pending:$onlyThisYear");
        return $this->cache($key,$forceRefresh ,fn() => $this->pending->execute($onlyThisYear));
    }


    public function getAllStudents(bool $onlyThisYear, bool $forceRefresh): int
    {
        $key = $this->service->makeKey(CachePrefix::STAFF->value, StaffCacheSufix::DASHBOARD->value . ":students:$onlyThisYear");
        return $this->cache($key,$forceRefresh ,fn() => $this->students->execute($onlyThisYear));
    }


    public function paymentsMade(bool $onlyThisYear, bool $forceRefresh):FinancialSummaryResponse
    {
        $key = $this->service->makeKey(CachePrefix::STAFF->value, StaffCacheSufix::DASHBOARD->value . ":payments:$onlyThisYear");
        return $this->cache($key,$forceRefresh ,fn() => $this->payments->execute($onlyThisYear));
    }

    public function getAllConcepts(bool $onlyThisYear, int $perPage, int $page, bool $forceRefresh):PaginatedResponse
    {
        $key = $this->service->makeKey(CachePrefix::STAFF->value, StaffCacheSufix::DASHBOARD->value . ":concepts:$perPage:$page:$onlyThisYear");
        return $this->cache($key,$forceRefresh ,fn() => $this->concepts->execute($onlyThisYear, $perPage, $page));

    }

    public function createPayout(): StripePayoutResponse
    {
        $create= $this->payout->execute();
        $this->service->clearKey(CachePrefix::STAFF->value, StaffCacheSufix::DASHBOARD->value . ":payments:true");
        return $create;
    }

    public function refreshAll(): void
    {
        $this->service->clearKey(CachePrefix::STAFF->value, StaffCacheSufix::DASHBOARD->value);
    }

}
