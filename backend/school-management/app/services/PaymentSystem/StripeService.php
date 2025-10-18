<?php

namespace App\Services\PaymentSystem;
use App\Models\User;
use Stripe\Stripe;
use Stripe\Customer;
use Stripe\PaymentMethod as StripePaymentMethod;
use App\Models\PaymentConcept;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\CardException;
use Stripe\Exception\RateLimitException;
use App\Utils\Validators\StripeValidator;
use App\Utils\Validators\PaymentConceptValidator;
use Stripe\Checkout\Session;
use Stripe\SetupIntent;


class StripeService{

    public function __construct(){
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function createStripeUser(User $user){
        try{
            StripeValidator::ensureUserHasEmailAndName($user);

            if($user->stripe_customer_id){
                return $user->stripe_customer_id;
            }

            $existingCustomers = Customer::all([
                'email' => $user->email,
                'limit' => 1,
            ]);

            if(count($existingCustomers->data) > 0){
                $user->stripe_customer_id = $existingCustomers->data[0]->id;
                $user->save();
                return $user->stripe_customer_id;
            }

            $customer = Customer::create([
                'email' => $user->email,
                'name' => $user->name . ' ' . $user->last_name
            ]);

            $user->stripe_customer_id = $customer->id;
            $user->save();

            return $user->stripe_customer_id;

        }catch (\InvalidArgumentException $e) {
            throw $e;
        }catch(ApiErrorException $e){
            logger()->error("Stripe error creating customer: " . $e->getMessage());
            throw $e;
        }
    }


    public function createSetupSession(User $user){

        try{
            $customerId = $this->createStripeUser($user);

            $session = Session::create([
            'mode' => 'setup',
            'payment_method_types' => ['card'],
            'customer' => $customerId,
            'success_url' => config('app.frontend_url') . '/setup-success?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => config('app.frontend_url') . '/setup-cancel',
        ]);

        return $session;
        }catch(ApiErrorException $e){
            logger()->error("Stripe error setupSession: " . $e->getMessage());
            throw $e;
        }catch (RateLimitException $e) {
            logger()->error("Rate limit hit: " . $e->getMessage());
            throw $e;
        }

    }

    public function getSetupIntentFromSession(string $sessionId)
    {
        try {
            $session = Session::retrieve($sessionId);

            if (empty($session->setup_intent)) {
                return null;
            }

            $setupIntent = SetupIntent::retrieve($session->setup_intent);

            return $setupIntent;
        } catch (ApiErrorException $e) {
            logger()->error("Stripe error retrieving setup_intent from session: " . $e->getMessage());
            throw $e;
        }
    }

    public function retrievePaymentMethod(string $paymentMethodId)
    {
        return StripePaymentMethod::retrieve($paymentMethodId);
    }

    public function createCheckoutSession(User $user, PaymentConcept $concept)
    {
        try{
            $customerId = $this->createStripeUser($user);
            StripeValidator::ensureValidConcept($concept);

            PaymentConceptValidator::ensureConceptIsActiveAndValid($user,$concept);

            $sessionData = [
            'mode' => 'payment',
            'customer' => $customerId,
            'customer_update' => [
                'address' => 'auto',
            ],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'mxn',
                    'product_data' => [
                        'name' => $concept->concept_name,
                    ],
                    'unit_amount' => intval($concept->amount * 100),
                ],
                'quantity' => 1,
            ]],
            'payment_method_types' => ['card', 'oxxo', 'customer_balance'],


            'metadata' => [
                'payment_concept_id' => $concept->id,
             ],
             'payment_method_options' => [
                'card' => [
                    'setup_future_usage' => 'off_session',
                ],
                'customer_balance'=>[
                    'funding_type' => 'bank_transfer',
                    'bank_transfer' => ['type' => 'mx_bank_transfer']
                ],
            ],
            'saved_payment_method_options' => [
                'payment_method_save' => 'enabled',
            ],

            'success_url' => config('app.frontend_url') . '/payment-success?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => config('app.frontend_url') . '/payment-cancel',

        ];


        $session = Session::create($sessionData);

        return $session;

        }catch(ApiErrorException $e){
            logger()->error("Stripe error checkout session: " . $e->getMessage());
            throw $e;
        }catch (RateLimitException $e) {
            logger()->error("Rate limit hit: " . $e->getMessage());
            throw $e;
        }


    }

    public function showPaymentMethods(User $user){

        try{
            $customerId = $this->createStripeUser($user);
            $paymentMethods= StripePaymentMethod::all([
                'customer'=>$customerId,
                'type'=>'card'
            ]);
            return $paymentMethods;
        }catch (\InvalidArgumentException $e) {
            throw $e;
        }catch(ApiErrorException $e){
            logger()->error("Stripe error showing PaymentMethods: " . $e->getMessage());
            throw $e;
        }catch (RateLimitException $e) {
            logger()->error("Rate limit hit: " . $e->getMessage());
            throw $e;

        }

    }

    public function deletePaymentMethod(string $stripePaymentMethodId)
    {
        try {
            StripeValidator::ensureValidPaymentMethodId($stripePaymentMethodId);
            $stripePM = StripePaymentMethod::retrieve($stripePaymentMethodId);
            $stripePM->detach();
            return true;
        }catch (\InvalidArgumentException $e) {
            throw $e;
        }catch (\Stripe\Exception\ApiErrorException $e) {
            logger()->error("Stripe error detaching PaymentMethod: " . $e->getMessage());
            throw $e;
        }
    }


}


