<?php

namespace App\Core\Application\Services\Payments\Staff;

use App\Core\Application\DTO\Response\General\PaginatedResponse;
use App\Core\Application\Traits\HasCache;
use App\Core\Application\UseCases\Payments\Staff\Payments\ShowAllPaymentsByConceptNameUseCase;
use App\Core\Application\UseCases\Payments\Staff\Payments\ShowAllPaymentUseCase;
use App\Core\Domain\Enum\Cache\CachePrefix;
use App\Core\Domain\Enum\Cache\StaffCacheSufix;
use App\Core\Infraestructure\Cache\CacheService;

class PaymentsService{
    use HasCache;
    public function __construct(
        private ShowAllPaymentUseCase $payments,
        private ShowAllPaymentsByConceptNameUseCase $paymentsByConceptName,
        private CacheService $service
    )
    {
        $this->setCacheService($service);
    }
    public function showAllPayments(?string $search, int $perPage, int $page,  bool $forceRefresh): PaginatedResponse
    {
        $key = $this->service->makeKey(CachePrefix::STAFF->value, StaffCacheSufix::PAYMENTS->value . ":show:$search:$perPage:$page");
        return $this->cache($key,$forceRefresh ,fn() =>$this->payments->execute($search,$perPage, $page));
    }

    public function showAllPaymentsByConceptName(?string $search, int $perPage, int $page,  bool $forceRefresh): PaginatedResponse
    {
        $key = $this->service->makeKey(CachePrefix::STAFF->value, StaffCacheSufix::PAYMENTS->value . ":show-by-name:$search:$perPage:$page");
        return $this->cache($key,$forceRefresh ,fn() =>$this->payments->execute($search,$perPage, $page));
    }

}
