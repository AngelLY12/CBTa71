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


class StripeService{

    public function createStripeUser(User $user){
        try{
            StripeValidator::ensureUserHasEmailAndName($user);

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
        }catch (\InvalidArgumentException $e) {
            throw $e;
        }catch(ApiErrorException $e){
            logger()->error("Stripe error creating customer: " . $e->getMessage());
            throw new \Exception("No se pudo asociar el usuario.");
        }

    }

    public function createSetupIntent(User $user){

        try{
            Stripe::setApiKey(config('services.stripe.secret'));

            $customerId = $this->createStripeUser($user);

            $setupIntent = SetupIntent::create([
                'customer'=> $customerId,
                'payment_method_types' => ['card']
            ]);

            return $setupIntent;
        }catch(ApiErrorException $e){
            logger()->error("Stripe error setupIntent: " . $e->getMessage());
            throw new \Exception("Hubo un error en la solicitud.");
        }catch (RateLimitException $e) {
            logger()->error("Rate limit hit: " . $e->getMessage());
            throw new \Exception("Demasiadas solicitudes, intenta más tarde.");

        }

    }

    public function createStripePaymentMethod(string $paymentMethodId, User $user){
        try{
            StripeValidator::ensureValidPaymentMethodId($paymentMethodId);
            Stripe::setApiKey(config('services.stripe.secret'));
            $stripePaymentMethod = StripePaymentMethod::retrieve($paymentMethodId);
            $stripePaymentMethod->attach(['customer' => $user->stripe_customer_id]);
            return $stripePaymentMethod;

        }catch (\InvalidArgumentException $e) {
            throw $e;
        }catch(ApiErrorException $e){
            logger()->error("Stripe error attaching PaymentMethod: " . $e->getMessage());
            throw new \Exception("No se pudo asociar el método de pago.");
        }

    }

    public function createPaymentIntent(User $user,PaymentConcept $concept, string $paymentMethodId){

        try {

            StripeValidator::ensureUserHasStripeCustomer($user);
            StripeValidator::ensureValidPaymentMethodId($paymentMethodId);
            StripeValidator::ensureValidConcept($concept);
            StripeValidator::ensureExistsPaymentMethodId($paymentMethodId, $user);

            Stripe::setApiKey(config('services.stripe.secret'));

            $paymentIntent = PaymentIntent::create([
                'amount'=>intval($concept->amount*100),
                'currency'=>'mxn',
                'customer'=>$user->stripe_customer_id,
                'payment_method'=>$paymentMethodId,
                'off_session'=>true,
                'confirm'=>true

            ]);

            return $paymentIntent;

        }catch (\InvalidArgumentException $e) {
            throw $e;
        }catch(ApiErrorException $e){
            logger()->error("Stripe error creating PaymentIntent: " . $e->getMessage());
            throw new \Exception("No se pudo crear el PaymentIntent. Intenta más tarde.");

        }catch (CardException $e) {
            logger()->warning("Card declined for user {$user->id}: " . $e->getError()->message);
            throw new \Exception("Tu tarjeta fue rechazada: " . $e->getError()->message);

        }catch (RateLimitException $e) {
            logger()->error("Rate limit hit: " . $e->getMessage());
            throw new \Exception("Demasiadas solicitudes, intenta más tarde.");

        }

    }

    public function showPaymentMethods(User $user){

        try{
            StripeValidator::ensureUserHasStripeCustomer($user);
            Stripe::setApiKey(config('services.stripe.secret'));
            $paymentMethods= StripePaymentMethod::all([
                'customer'=>$user->stripe_customer_id,
                'type'=>'card'
            ]);
            return $paymentMethods;
        }catch (\InvalidArgumentException $e) {
            throw $e;
        }catch(ApiErrorException $e){
            logger()->error("Stripe error showing PaymentMethods: " . $e->getMessage());
            throw new \Exception("No es posible mostrar las tarjetas del usuario. Intenta más tarde.");
        }catch (RateLimitException $e) {
            logger()->error("Rate limit hit: " . $e->getMessage());
            throw new \Exception("Demasiadas solicitudes, intenta más tarde.");

        }



    }

    public function deletePaymentMethod(string $stripePaymentMethodId)
    {
    try {
        StripeValidator::ensureValidPaymentMethodId($stripePaymentMethodId);
        Stripe::setApiKey(config('services.stripe.secret'));
        $stripePM = StripePaymentMethod::retrieve($stripePaymentMethodId);
        $stripePM->detach();
        return true;
    }catch (\InvalidArgumentException $e) {
        throw $e;
    }catch (\Stripe\Exception\ApiErrorException $e) {
        logger()->error("Stripe error detaching PaymentMethod: " . $e->getMessage());
        throw new \Exception("No se pudo eliminar el método de pago. Intenta más tarde.");
    }
    }


}


