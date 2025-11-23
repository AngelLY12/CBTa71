<?php

namespace App\Core\Application\Services\Payments\Staff;

use App\Core\Application\DTO\Response\General\PaginatedResponse;
use App\Core\Application\Traits\HasCache;
use App\Core\Application\UseCases\Payments\Staff\Payments\ShowAllPaymentUseCase;

class PaymentsService{
    use HasCache;
    public function __construct(
        private ShowAllPaymentUseCase $payments,
    )
    {
    }
    public function showAllPayments(?string $search, int $perPage, int $page,  bool $forceRefresh): PaginatedResponse
    {
        $key = "staff:payments:show:$search:$perPage:$page";
        return $this->cache($key,$forceRefresh ,fn() =>$this->payments->execute($search,$perPage, $page));
    }
}
