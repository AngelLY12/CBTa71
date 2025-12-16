<?php

namespace App\Core\Application\UseCases\Payments\Student\Dashboard;

use App\Core\Domain\Repositories\Query\Payments\PaymentQueryRepInterface;

class PaymentsMadeUseCase
{
      public function __construct(
        private PaymentQueryRepInterface $pqRepo,

    ) {}
    public function execute(int $userId, bool $onlyThisYear): string {
        return $this->pqRepo->sumPaymentsByUserYear($userId, $onlyThisYear);
    }
}
