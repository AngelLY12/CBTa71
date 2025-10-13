<?php

namespace App\Services\PaymentSystem\Student;

use App\Models\User;
use App\Models\PaymentMethod;
use App\Services\PaymentSystem\StripeService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CardsService{
protected StripeService $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    public function savedPaymentMethod(User $user){
        return $this->stripeService->createSetupSession($user);
    }

     public function finalizeSetupFromSessionId(string $sessionId)
    {
        return $this->stripeService->getSetupIntentFromSession($sessionId);
    }

     public function getPaymentMethodDetails(User $user,string $paymentMethodId)
    {
        $paymentMethod = $this->stripeService->retrievePaymentMethod($paymentMethodId);
        if (!$paymentMethod || !$paymentMethod->card) {
            throw ValidationException::withMessages([
                'payment_method' => 'No se pudo obtener la información del método de pago.',
            ]);
        }

        PaymentMethod::create([
        'user_id' => $user->id,
        'stripe_payment_method_id' => $paymentMethod->id,
        'brand' => $paymentMethod->card->brand ?? null,
        'last4' => $paymentMethod->card->last4 ?? null,
        'bank_name' => $paymentMethod->card->bank ?? null,
        'exp_month' => $paymentMethod->card->exp_month ?? null,
        'exp_year' => $paymentMethod->card->exp_year ?? null,
    ]);
        return $paymentMethod;
    }

    public function showPaymentMethods(User $user){
         return $user->paymentMethods()
            ->makeHidden(['created_at', 'updated_at']);
    }

    public function deletePaymentMethod($stripePaymentMethodId){
        return DB::transaction(function() use ($stripePaymentMethodId){
            $localMethod = PaymentMethod::where('stripe_payment_method_id', $stripePaymentMethodId)->first();

            if (!$localMethod) {
                throw ValidationException::withMessages([
                    'payment_method' => 'El método de pago no existe en el sistema.',
                ]);
            }

            $this->stripeService->deletePaymentMethod($stripePaymentMethodId);
            $localMethod->delete();

            return true;
        });
    }

}
