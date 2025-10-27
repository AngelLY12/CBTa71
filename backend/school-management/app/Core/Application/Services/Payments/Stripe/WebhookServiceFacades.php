<?php

namespace App\Core\Application\Services\Payments\Stripe;

use App\Core\Application\UseCases\Payments\Stripe\HandleFailedOrExpiredPaymentUseCase;
use App\Core\Application\UseCases\Payments\Stripe\PaymentMethodAttachedUseCase;
use App\Core\Application\UseCases\Payments\Stripe\RequiresActionUseCase;
use App\Core\Application\UseCases\Payments\Stripe\SessionAsyncCompletedUseCase;
use App\Core\Application\UseCases\Payments\Stripe\SessionCompletedUseCase;
use Stripe\Stripe;

class WebhookServiceFacades{

    public function __construct(
       private SessionCompletedUseCase $session,
       private SessionAsyncCompletedUseCase $async,
       private PaymentMethodAttachedUseCase $attached,
       private RequiresActionUseCase $requires,
       private HandleFailedOrExpiredPaymentUseCase $handle
    ) {
        Stripe::setApiKey(config('services.stripe.secret'));

    }

    public function sessionCompleted($obj)
    {
        return $this->session->execute($obj);
    }

    public function sessionAsync($obj) {
        return $this->async->execute($obj);
    }

    public function paymentMethodAttached($obj){

       return $this->attached->execute($obj);
    }

    public function requiresAction($obj){
        return $this->requires->execute($obj);
    }

    public function handleFailedOrExpiredPayment($obj, string $eventType)
    {
        return $this->handle->execute($obj,$eventType);

    }
}
