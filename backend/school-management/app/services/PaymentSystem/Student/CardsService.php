<?php

namespace App\Services\PaymentSystem\Student;

use Stripe\Stripe;
use App\Models\User;
use Stripe\PaymentMethod as StripePaymentMethod;
use App\Models\PaymentMethod;


class CardsService{


    public function savedPaymentMethod(string $paymentMethodId, User $user){
        Stripe::setApiKey(config('services.stripe.secret'));
        $stripePaymentMethod = StripePaymentMethod::retrieve($paymentMethodId);
        $stripePaymentMethod->attach(['customer' => $user->stripe_customer_id]);

        return PaymentMethod::create([
            'user_id' => $user->id,
            'stripe_payment_method_id' => $paymentMethodId,
        ]);

    }

    public function showPaymentMethods(User $user){
        Stripe::setApiKey(config('services.stripe.secret'));
        $paymentMethods = PaymentMethod::where('user_id',$user->id)->get();
        $cards =[];
        foreach($paymentMethods as $pm){
            $stripePM = StripePaymentMethod::retrieve($pm->stripe_payment_method_id);
            $cards[]=[
                'id'       => $pm->id,
                'brand'    => $stripePM->card->brand,
                'last4'    => $stripePM->card->last4,
                'exp_month'=> $stripePM->card->exp_month,
                'exp_year' => $stripePM->card->exp_year,
                'funding'  => $stripePM->card->funding,
                'country'  => $stripePM->card->country,
                'pm_id'    => $pm->stripe_payment_method_id

            ];
        }
        return $cards;
    }

    public function deletePaymentMethod(PaymentMethod $pm){
        Stripe::setApiKey(config('services.stripe.secret'));
        $stripePM=StripePaymentMethod::retrieve($pm->stripe_payment_method_id);
        $stripePM->detach();

        $pm->delete();

        return true;


    }

}
