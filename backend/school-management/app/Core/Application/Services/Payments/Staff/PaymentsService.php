<?php

namespace App\Core\Application\Services\Payments\Staff;

use App\Core\Application\DTO\Response\General\PaginatedResponse;
use App\Core\Application\Mappers\GeneralMapper;
use App\Core\Application\Mappers\PaymentMapper;
use App\Core\Application\Traits\HasCache;
use App\Core\Application\UseCases\Payments\Staff\Payments\ShowAllPaymentUseCase;
use App\Core\Domain\Repositories\Query\Payments\PaymentQueryRepInterface;
use App\Core\Infraestructure\Cache\CacheService;

class PaymentsService{
    use HasCache;
    public function __construct(
        private ShowAllPaymentUseCase $payments,
    )
    {
    }
    public function showAllPayments(?string $search, int $perPage, int $page,  bool $forceRefresh): PaginatedResponse
    {
        $key = "payments:show:$search:$perPage:$page";
        return $this->cache($key,$forceRefresh ,fn() =>$this->payments->execute($search,$perPage, $page));
    }
}
