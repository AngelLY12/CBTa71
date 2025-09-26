<?php

namespace App\Services\PaymentSystem\Student;

use App\Models\User;
use Stripe\Stripe;
use Stripe\PaymentIntent;


class PaymentHistory{

    public function paymentHistory(User $user){
        Stripe::setApiKey(config('services.stripe.secret'));
        $payments = $user->payments()
        ->with('paymentConcept:id,concept_name,description,amount')

        ->orderBy('transaction_date','desc')
        ->get()
        ->map(function($payment){
            $intent = PaymentIntent::retrieve($payment->payment_intent_id);
            $charge = $intent->charges->data[0] ?? null;
            return [
            'id'=>$payment->id,
            'concepto'=>$payment->paymentConcept->concept_name ?? null,
            'descripcion'=>$payment->paymentConcept->description ?? null,
            'monto'=>$payment->paymentConcept->amount ?? null,
            'fecha'=>$payment->transaction_date,
            'estatus'=>$payment->status,
            'url' => $payment->url ?? null,
            'tarjeta'     => $charge ? [
                'brand'     => $charge->payment_method_details->card->brand,
                'last4'     => $charge->payment_method_details->card->last4,
            ] : null,
            ];

        });

        return $payments;

    }
}
