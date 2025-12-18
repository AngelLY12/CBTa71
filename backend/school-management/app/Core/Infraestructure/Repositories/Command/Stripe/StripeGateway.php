<?php
namespace App\Core\Infraestructure\Repositories\Command\Stripe;

use App\Core\Domain\Entities\PaymentConcept;
use App\Core\Domain\Entities\User;
use App\Core\Domain\Enum\Payment\PaymentStatus;
use App\Core\Domain\Repositories\Command\Stripe\StripeGatewayInterface;
use App\Core\Domain\Utils\Validators\StripeValidator;
use App\Exceptions\StripeGatewayException;
use InvalidArgumentException;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Customer;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\RateLimitException;
use Stripe\PaymentMethod as StripePaymentMethod;

class StripeGateway implements StripeGatewayInterface
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function createStripeUser(User $user): string
    {
        StripeValidator::validateUserForStripe($user);

        try{
            if ($user->stripe_customer_id) {
                return $user->stripe_customer_id;
            }

            $existingCustomers = Customer::all(['email' => $user->email, 'limit' => 1]);

            if (count($existingCustomers->data) > 0) {
                $user->stripe_customer_id = $existingCustomers->data[0]->id;
                return $user->stripe_customer_id;
            }

            $customer = Customer::create([
                'email' => $user->email,
                'name' => $user->fullName(),
            ]);

            $user->stripe_customer_id = $customer->id;
            return $user->stripe_customer_id;
        }catch (\InvalidArgumentException $e) {
            throw $e;
        }catch(ApiErrorException $e){
            logger()->error("Stripe error creating customer: " . $e->getMessage());
            throw new StripeGatewayException("Error al crear el cliente en Stripe", 500);

        }

    }

    public function createSetupSession(User $user): Session
    {
        $customerId = $this->createStripeUser($user);
        try{
            return Session::create([
                'mode' => 'setup',
                'payment_method_types' => ['card'],
                'customer' => $customerId,
                'success_url' => config('app.frontend_url') . '/setup-success?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => config('app.frontend_url') . '/setup-cancel',
            ]);
        }catch(ApiErrorException $e){
            logger()->error("Stripe error setupSession: " . $e->getMessage());
            throw new StripeGatewayException("Error al crear la sesión setup", 500);
        }catch (RateLimitException $e) {
            logger()->error("Rate limit hit: " . $e->getMessage());
            throw new StripeGatewayException("Limite de peticiones superado, espera un momento", 500);
        }

    }

     public function createCheckoutSession(User $user, PaymentConcept $paymentConcept, string $amount): Session
    {
        $customerId = $this->createStripeUser($user);
        try{
            $sessionData = [
            'mode' => 'payment',
            'customer' => $customerId,
            'customer_update' => ['address' => 'auto'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'mxn',
                    'product_data' => ['name' => $paymentConcept->concept_name],
                    'unit_amount' => (int) bcmul($amount, '100', 0),
                ],
                'quantity' => 1,
            ]],
            'payment_method_types' => ['card', 'oxxo', 'customer_balance'],
            'metadata' => ['payment_concept_id' => $paymentConcept->id, 'concept_name' => $paymentConcept->concept_name],
            'payment_method_options' => [
                'card' => ['setup_future_usage' => 'off_session'],
                'customer_balance' => [
                    'funding_type' => 'bank_transfer',
                    'bank_transfer' => ['type' => 'mx_bank_transfer'],
                ],
            ],
            'saved_payment_method_options' => ['payment_method_save' => 'enabled'],
            'success_url' => config('app.frontend_url') . '/payment-success?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => config('app.frontend_url') . '/payment-cancel',
        ];

        return Session::create($sessionData);

        }catch(ApiErrorException $e){
            logger()->error("Stripe error checkout session: " . $e->getMessage());
            throw new StripeGatewayException("Error al crear la sesión", 500);
        }catch (RateLimitException $e) {
            logger()->error("Rate limit hit: " . $e->getMessage());
            throw new StripeGatewayException("Se alcanzo el limite de intentos, espera un momento", 500);
        }

    }

    public function deletePaymentMethod(string $paymentMethodId): bool
    {
        StripeValidator::validateStripeId($paymentMethodId,'pm','método de pago');
        try{
            $pm = StripePaymentMethod::retrieve($paymentMethodId);
            $pm->detach();
            return true;
        }catch (InvalidArgumentException $e) {
            throw $e;
        }catch (ApiErrorException $e) {
            logger()->error("Stripe error detaching PaymentMethod: " . $e->getMessage());
            throw new StripeGatewayException("Error eliminando el método de pago", 500);
        }

    }
    public function expireSessionIfPending(string $sessionId): bool
    {
        StripeValidator::validateStripeId($sessionId,'cs','ID de la sesión');
        try {
            $session = Session::retrieve($sessionId);

            if (in_array($session->payment_status, PaymentStatus::nonPaidStatuses())) {
                $session->expire();
                return true;
            }

            return false;
        } catch (\Exception $e) {
            logger()->warning("No se pudo expirar la sesión {$sessionId}: " . $e->getMessage());
            return false;
        }
    }
}
