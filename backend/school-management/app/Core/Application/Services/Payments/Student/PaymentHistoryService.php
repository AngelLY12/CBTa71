<?php
namespace App\Core\Application\Services\Payments\Student;

use App\Core\Application\DTO\Response\General\PaginatedResponse;
use App\Core\Application\Traits\HasCache;
use App\Core\Application\UseCases\Payments\Student\PaymentHistory\FindPaymentByIdUseCase;
use App\Core\Application\UseCases\Payments\Student\PaymentHistory\GetPaymentHistoryUseCase;
use App\Core\Domain\Entities\Payment;
use App\Core\Domain\Entities\User;
use App\Core\Domain\Enum\Cache\CachePrefix;
use App\Core\Domain\Enum\Cache\StudentCacheSufix;
use App\Core\Infraestructure\Cache\CacheService;

class PaymentHistoryService {
    use HasCache;
    public function __construct(
        private GetPaymentHistoryUseCase $history,
        private FindPaymentByIdUseCase $payment,
        private CacheService $service
    ) {
        $this->setCacheService($service);
    }

    public function paymentHistory(User $user, int $perPage, int $page, bool $forceRefresh): PaginatedResponse {
        $key = $this->service->makeKey(CachePrefix::STUDENT->value, StudentCacheSufix::HISTORY->value . ":$user->id:$perPage:$page");
        return $this->cache($key,$forceRefresh ,fn() => $this->history->execute($user->id, $perPage, $page));
    }

    public function findPayment(int $id): Payment
    {
        return $this->payment->execute($id);
    }

}
