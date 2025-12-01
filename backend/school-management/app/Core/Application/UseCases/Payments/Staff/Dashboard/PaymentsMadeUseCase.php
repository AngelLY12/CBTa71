<?php

namespace App\Core\Application\UseCases\Payments\Staff\Dashboard;

use App\Core\Domain\Repositories\Query\Payments\PaymentQueryRepInterface;

class PaymentsMadeUseCase{
 public function __construct(
        private PaymentQueryRepInterface $pqRepo,
    )
    {
    }
    public function execute(bool $onlyThisYear):string
    {
        return $this->pqRepo->getAllPaymentsMade($onlyThisYear);

    }
}
