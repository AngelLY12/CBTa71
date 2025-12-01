<?php

namespace App\Core\Application\Services\Payments\Student;

use App\Core\Application\Traits\HasCache;
use App\Core\Application\UseCases\Payments\Student\PendingPayment\PayConceptUseCase;
use App\Core\Application\UseCases\Payments\Student\PendingPayment\ShowOverduePaymentsUseCase;
use App\Core\Application\UseCases\Payments\Student\PendingPayment\ShowPendingPaymentsUseCase;
use App\Core\Domain\Entities\User;
use App\Core\Domain\Enum\Cache\CachePrefix;
use App\Core\Domain\Enum\Cache\StudentCacheSufix;
use App\Core\Infraestructure\Cache\CacheService;

class PendingPaymentServiceFacades{
    use HasCache;
    public function __construct(
        private ShowPendingPaymentsUseCase $pending,
        private ShowOverduePaymentsUseCase $overdue,
        private PayConceptUseCase $pay,
        private CacheService $service,
    ) {}

    public function showPendingPayments(User $user, bool $forceRefresh): array {
        $key = $this->service->makeKey(CachePrefix::STUDENT->value, StudentCacheSufix::PENDING->value . ":pending:$user->id");
        return $this->cache($key,$forceRefresh ,fn() =>  $this->pending->execute($user->id));
    }

    public function showOverduePayments(User $user, bool $forceRefresh): array {
        $key = $this->service->makeKey(CachePrefix::STUDENT->value, StudentCacheSufix::PENDING->value . ":overdue:$user->id");
        return $this->cache($key,$forceRefresh ,fn() => $this->overdue->execute($user->id));
    }

    public function payConcept(User $user, int $conceptId): string {
        $pay=$this->pay->execute($user->id,$conceptId);
        $this->service->clearKey(CachePrefix::STUDENT->value, StudentCacheSufix::PENDING->value . ":pending:$user->id");
        $this->service->clearKey(CachePrefix::STUDENT->value, StudentCacheSufix::HISTORY->value .":$user->id");
        return $pay;
    }

}
