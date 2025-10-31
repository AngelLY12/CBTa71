<?php

namespace App\Core\Application\UseCases\Payments\Stripe;

use App\Core\Application\Traits\HasPaymentSession;
use Stripe\Stripe;

class SessionAsyncCompletedUseCase
{
   use HasPaymentSession;

    public function execute($obj) {
        return $this->handlePaymentSession($obj, [
        'status' => $obj->payment_status,
        ]);

    }
}
