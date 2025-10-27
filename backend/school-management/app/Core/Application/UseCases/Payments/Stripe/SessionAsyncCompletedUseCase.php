<?php

namespace App\Core\Application\UseCases\Payments\Stripe;

use Stripe\Stripe;

class SessionAsyncCompletedUseCase
{
    public function __construct(
        private HandlePaymentSessionUseCase $handle
    ) {
        Stripe::setApiKey(config('services.stripe.secret'));

    }

    public function execute($obj) {
        return $this->handle->execute($obj, [
        'status' => $obj->payment_status,
        ]);

    }
}
