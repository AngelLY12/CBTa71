<?php

namespace App\Core\Application\Services\Payments\Student;

use App\Core\Application\Traits\HasCache;
use App\Core\Application\UseCases\Payments\Student\PendingPayment\PayConceptUseCase;
use App\Core\Application\UseCases\Payments\Student\PendingPayment\ShowOverduePaymentsUseCase;
use App\Core\Application\UseCases\Payments\Student\PendingPayment\ShowPendingPaymentsUseCase;
use App\Core\Domain\Entities\User;
use App\Core\Infraestructure\Cache\CacheService;

class PendingPaymentServiceFacades{
    use HasCache;
    private string $prefix='pending';
    public function __construct(
        private ShowPendingPaymentsUseCase $pending,
        private ShowOverduePaymentsUseCase $overdue,
        private PayConceptUseCase $pay,
        private CacheService $service,
    ) {}

    public function showPendingPayments(User $user, bool $forceRefresh): array {
        $key = "$this->prefix:pending:$user->id";
        return $this->cache($key,$forceRefresh ,fn() =>  $this->pending->execute($user->id));
    }

    public function showOverduePayments(User $user, bool $forceRefresh): array {
        $key = "$this->prefix:overdue:$user->id";
        return $this->cache($key,$forceRefresh ,fn() => $this->overdue->execute($user->id));
    }

    public function payConcept(User $user, int $conceptId): string {
        $pay=$this->pay->execute($user->id,$conceptId);
        $this->service->forget("pending:pending:$user->id");
        return $pay;
    }

}
