<?php

namespace App\Core\Application\Services\Payments\Staff;

use App\Core\Application\DTO\Response\General\DashboardDataResponse;
use App\Core\Application\DTO\Response\General\PaginatedResponse;
use App\Core\Application\DTO\Response\PaymentConcept\PendingSummaryResponse;
use App\Core\Application\Mappers\GeneralMapper;
use App\Core\Application\Traits\HasCache;
use App\Core\Application\UseCases\Payments\Staff\Dashboard\GetAllConceptsUseCase;
use App\Core\Application\UseCases\Payments\Staff\Dashboard\GetAllStudentsUseCase;
use App\Core\Application\UseCases\Payments\Staff\Dashboard\PaymentsMadeUseCase;
use App\Core\Application\UseCases\Payments\Staff\Dashboard\PendingPaymentAmountUseCase;
use App\Core\Infraestructure\Cache\CacheService;

class DashboardServiceFacades{
    use HasCache;
    private string $prefix= 'dashboard';
    public function __construct(
        private PendingPaymentAmountUseCase $pending,
        private GetAllStudentsUseCase $students,
        private PaymentsMadeUseCase $payments,
        private GetAllConceptsUseCase $concepts,
        private CacheService $service
    )
    {
    }

    public function pendingPaymentAmount(bool $onlyThisYear, bool $forceRefresh): PendingSummaryResponse
    {
        $key = "$this->prefix:pending:$onlyThisYear";
        return $this->cache($key,$forceRefresh ,fn() => $this->pending->execute($onlyThisYear));
    }


    public function getAllStudents(bool $onlyThisYear, bool $forceRefresh): int
    {
        $key = "$this->prefix:students:$onlyThisYear";
        return $this->cache($key,$forceRefresh ,fn() => $this->students->execute($onlyThisYear));
    }


    public function paymentsMade(bool $onlyThisYear, bool $forceRefresh):string
    {
        $key = "$this->prefix:payments:$onlyThisYear";
        return $this->cache($key,$forceRefresh ,fn() => $this->payments->execute($onlyThisYear));
    }

    public function getAllConcepts(bool $onlyThisYear, int $perPage, int $page, bool $forceRefresh):PaginatedResponse
    {
        $key = "$this->prefix:concepts:$onlyThisYear:$perPage:$page";
        return $this->cache($key,$forceRefresh ,fn() => $this->concepts->execute($onlyThisYear, $perPage, $page));

    }

    public function getData(bool $onlyThisYear, bool $forceRefresh):DashboardDataResponse
    {
        return GeneralMapper::toDashboardDataResponse(
            $this->paymentsMade($onlyThisYear, $forceRefresh),
            $this->pendingPaymentAmount($onlyThisYear, $forceRefresh),
            $this->getAllStudents($onlyThisYear, $forceRefresh));
    }

    public function refreshAll(): void
    {
        $this->service->clearPrefix($this->prefix);
    }

}
