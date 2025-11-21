<?php

namespace App\Core\Application\UseCases\Payments\Stripe;

use App\Core\Application\Mappers\EnumMapper;
use App\Core\Application\Traits\HasPaymentSession;
use Stripe\Stripe;

class SessionAsyncCompletedUseCase
{
   use HasPaymentSession;

    public function execute($obj) {
        $status=EnumMapper::fromStripe($obj->payment_status);
        return $this->handlePaymentSession($obj, [
        'status' => $status,
        ]);

    }
}
