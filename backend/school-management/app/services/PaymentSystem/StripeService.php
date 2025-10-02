<?php

namespace App\Services\PaymentSystem;
use App\Models\User;
use Stripe\Stripe;
use Stripe\Customer;
use Stripe\SetupIntent;
use Stripe\PaymentMethod as StripePaymentMethod;
use Stripe\PaymentIntent;
use App\Models\PaymentConcept;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\CardException;
use Stripe\Exception\RateLimitException;
use App\Utils\Validators\StripeValidator;
use App\Utils\Validators\PaymentConceptValidator;


class StripeService{

    public function __construct(){
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function createStripeUser(User $user){
        try{
            StripeValidator::ensureUserHasEmailAndName($user);

            if(!$user->stripe_customer_id){
                $customer = Customer::create([
                        'email'=>$user->email,
                        'name'=>$user->name . ' ' . $user->last_name
                    ]);

                $user->stripe_customer_id = $customer->id;
                $user->save();
            }

            return $user->stripe_customer_id;
        }catch (\InvalidArgumentException $e) {
            throw $e;
        }catch(ApiErrorException $e){
            logger()->error("Stripe error creating customer: " . $e->getMessage());
            throw $e;
        }

    }

    public function createSetupIntent(User $user){

        try{
            $customerId = $this->createStripeUser($user);

            $setupIntent = SetupIntent::create([
                'customer'=> $customerId,
                'payment_method_types' => ['card']
            ]);

            return $setupIntent;
        }catch(ApiErrorException $e){
            logger()->error("Stripe error setupIntent: " . $e->getMessage());
            throw $e;
        }catch (RateLimitException $e) {
            logger()->error("Rate limit hit: " . $e->getMessage());
            throw $e;
        }

    }

    public function createStripePaymentMethod(string $paymentMethodId, User $user){
        try{
            StripeValidator::ensureValidPaymentMethodId($paymentMethodId);
            $stripePaymentMethod = StripePaymentMethod::retrieve($paymentMethodId);
            $stripePaymentMethod->attach(['customer' => $user->stripe_customer_id]);
            return $stripePaymentMethod;

        }catch (\InvalidArgumentException $e) {
            throw $e;
        }catch(ApiErrorException $e){
            logger()->error("Stripe error attaching PaymentMethod: " . $e->getMessage());
            throw $e;
        }

    }

    public function createPaymentIntent(User $user,PaymentConcept $concept, string $paymentMethodId, string $paymentType = 'card'){

        try {

            StripeValidator::ensureValidPaymentType($paymentType);
            StripeValidator::ensureUserHasStripeCustomer($user);
            StripeValidator::ensureValidPaymentMethodId($paymentMethodId);
            StripeValidator::ensureValidConcept($concept);
            StripeValidator::ensureExistsPaymentMethodId($paymentMethodId, $user);
            PaymentConceptValidator::ensureConceptIsActiveAndValid($user,$concept);
            StripeValidator::ensureExistsPaymentMethodId($paymentMethodId, $user);

           $data = [
            'amount' => intval($concept->amount * 100),
            'currency' => 'mxn',
            'customer' => $user->stripe_customer_id,
            'payment_method_types' => [$paymentType],
            ];

            if ($paymentType === 'card' && $paymentMethodId) {
                $data['payment_method'] = $paymentMethodId;
                $data['off_session'] = true;
                $data['confirm'] = true;
            }

            if ($paymentType === 'bank_transfer') {
                $data['payment_method_options'] = [
                    'bank_transfer' => [
                        'type' => 'mxn_spei',
                    ],
                ];
            }

            return PaymentIntent::create($data);

        }catch (\InvalidArgumentException $e) {
            throw $e;
        }catch(ApiErrorException $e){
            logger()->error("Stripe error creating PaymentIntent: " . $e->getMessage());
            throw $e;

        }catch (CardException $e) {
            logger()->warning("Card declined for user {$user->id}: " . $e->getError()->message);
            throw $e;

        }catch (RateLimitException $e) {
            logger()->error("Rate limit hit: " . $e->getMessage());
            throw $e;

        }

    }

    public function showPaymentMethods(User $user){

        try{
            StripeValidator::ensureUserHasStripeCustomer($user);
            $paymentMethods= StripePaymentMethod::all([
                'customer'=>$user->stripe_customer_id,
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


