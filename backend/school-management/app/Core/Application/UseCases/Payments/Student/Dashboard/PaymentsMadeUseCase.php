<?php

namespace App\Core\Application\UseCases\Payments\Student\Dashboard;

use App\Core\Domain\Repositories\Query\Payments\PaymentQueryRepInterface;

class PaymentsMadeUseCase
{
      public function __construct(
        private PaymentQueryRepInterface $pqRepo,

    ) {}
    public function execute(int $userId): string {
        return $this->pqRepo->sumPaymentsByUserYear($userId);
    }
}
