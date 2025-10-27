<?php

namespace App\Core\Application\Services\Payments;

use App\Core\Application\UseCases\Payments\ReconcilePaymentUseCase;

class ReconcilePaymentsService
{
    public function __construct(
        private ReconcilePaymentUseCase $reconcile
    )
    {
    }
    public function reconcile():void
    {
        $this->reconcile->execute();
    }
}
