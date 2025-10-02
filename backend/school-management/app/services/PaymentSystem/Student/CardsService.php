<?php

namespace App\Services\PaymentSystem\Student;

use App\Models\User;
use App\Services\PaymentSystem\StripeService;

class CardsService{
protected StripeService $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    public function savedPaymentMethod(string $paymentMethodId, User $user){
        return $this->stripeService->createStripePaymentMethod($paymentMethodId, $user);
    }

    public function setupIntent(User $user){
        return $this->stripeService->createSetupIntent($user);

    }

    public function showPaymentMethods(User $user){

        $paymentMethods = $this->stripeService->showPaymentMethods($user);
        return array_map(fn($pm) => [
        'id'        => $pm->id,
        'brand'     => $pm->card->brand,
        'last4'     => $pm->card->last4,
        'exp_month' => $pm->card->exp_month,
        'exp_year'  => $pm->card->exp_year,
        'funding'   => $pm->card->funding,
        'country'   => $pm->card->country,
        ], $paymentMethods->data);
    }

    public function deletePaymentMethod($stripePaymentMethodId){
        $this->stripeService->deletePaymentMethod($stripePaymentMethodId);
        return true;
    }

}
