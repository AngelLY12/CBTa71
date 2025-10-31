<?php
namespace App\Core\Application\Services\Payments\Student;

use App\Core\Application\DTO\Response\General\PaginatedResponse;
use App\Core\Application\Traits\HasCache;
use App\Core\Application\UseCases\Payments\Student\PaymentHistory\GetPaymentHistoryUseCase;
use App\Core\Domain\Entities\User;
use App\Core\Infraestructure\Cache\CacheService;

class PaymentHistoryService {
    use HasCache;
    public function __construct(
        private GetPaymentHistoryUseCase $history,
    ) {}

    public function paymentHistory(User $user, int $perPage, int $page, bool $forceRefresh): PaginatedResponse {
        $key = "history:$user->id:$perPage:$page";
        return $this->cache($key,$forceRefresh ,fn() => $this->history->execute($user, $perPage, $page));
    }

}
