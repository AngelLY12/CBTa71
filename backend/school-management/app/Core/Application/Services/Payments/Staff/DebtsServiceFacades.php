<?php

namespace App\Core\Application\Services\Payments\Staff;

use App\Core\Application\DTO\Response\General\PaginatedResponse;
use App\Core\Application\DTO\Response\Payment\PaymentValidateResponse;
use App\Core\Application\Traits\HasCache;
use App\Core\Application\UseCases\Payments\Staff\Debts\GetPaymentsFromStripeUseCase;
use App\Core\Application\UseCases\Payments\Staff\Debts\ShowAllPendingPaymentsUseCase;
use App\Core\Application\UseCases\Payments\Staff\Debts\ValidatePaymentUseCase;
use App\Core\Domain\Enum\Cache\CachePrefix;
use App\Core\Domain\Enum\Cache\StaffCacheSufix;
use App\Core\Infraestructure\Cache\CacheService;

class DebtsServiceFacades{
    use HasCache;
    private const TAG_DEBTS_PENDING = [CachePrefix::STAFF->value, StaffCacheSufix::DEBTS->value, "pending"];

    public function __construct(
        private ShowAllPendingPaymentsUseCase $pending,
        private ValidatePaymentUseCase $validate,
        private GetPaymentsFromStripeUseCase $payments,
        private CacheService $service

    )
    {
        $this->setCacheService($service);

    }
    public function showAllpendingPayments(?string $search, int $perPage, int $page, bool $forceRefresh): PaginatedResponse
    {
        $key = $this->generateCacheKey(
            CachePrefix::STAFF->value,
            StaffCacheSufix::DEBTS->value . ":pending",
            [
                'search' => $search,
                'perPage' => $perPage,
                'page' => $page
            ]
        );

        return $this->shortCache(
            $key,
            fn() => $this->pending->execute($search, $perPage, $page),
            self::TAG_DEBTS_PENDING,
            $forceRefresh
        );
    }

    public function validatePayment(string $search, string $payment_intent_id): PaymentValidateResponse
    {
        $validate=$this->validate->execute($search,$payment_intent_id);
        $this->service->flushTags(self::TAG_DEBTS_PENDING);
        return $validate;
    }

    public function getPaymentsFromStripe(string $search, ?int $year, bool $forceRefresh):array
    {
        return $this->payments->execute($search,$year);
    }

}
