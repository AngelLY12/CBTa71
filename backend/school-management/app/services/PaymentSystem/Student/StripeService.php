<?php

namespace App\Services\PaymentSystem\Student;
use App\Models\User;
use Stripe\Stripe;
use Stripe\Customer;
use Stripe\SetupIntent;

class StripeService{

    public function createStripeUser(User $user){
        Stripe::setApiKey(config('services.stripe.secret'));
        if(!$user->stripe_customer_id){
            $customer = Customer::create([
                    'email'=>$user->email,
                    'name'=>$user->name . ' ' . $user->last_name
                ]);

            $user->stripe_customer_id = $customer->id;
            $user->save();
        }

        return $user->stripe_customer_id;
    }

    public function createSetupIntent(User $user){
        Stripe::setApiKey(config('services.stripe.secret'));

        $customerId = $this->createStripeUser($user);

        $setupIntent = SetupIntent::create([
            'customer'=> $customerId,
            'payment_method_types' => ['card']
        ]);

        return response()->json([
            'clientSecret' => $setupIntent->client_secret
        ]);


    }

}
