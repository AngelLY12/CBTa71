<?php

namespace App\Core\Infraestructure\Repositories\Query\Stripe;

use App\Core\Domain\Entities\User;
use App\Core\Domain\Repositories\Query\Stripe\StripeGatewayQueryInterface;
use App\Core\Domain\Utils\Validators\StripeValidator;
use App\Exceptions\StripeGatewayException;
use App\Exceptions\ValidationException;
use Stripe\Charge;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod as StripePaymentMethod;
use Stripe\SetupIntent;
use Stripe\Stripe;

class StripeGatewayQuery implements StripeGatewayQueryInterface
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }
    public function getSetupIntentFromSession(string $sessionId)
    {
        StripeValidator::validateStripeId($sessionId,'cs','ID de la sesión');
        try{
            $session = Session::retrieve($sessionId);
            if (empty($session->setup_intent)) return null;

            return SetupIntent::retrieve($session->setup_intent);
        }catch (ApiErrorException $e) {
            logger()->error("Stripe error retrieving setup_intent from session: " . $e->getMessage());
            throw new StripeGatewayException("Error trayendo el intent de la sesión", 500);
        }
    }

    public function retrievePaymentMethod(string $paymentMethodId)
    {
        StripeValidator::validateStripeId($paymentMethodId,'pm','método de pago');
        try {
            return StripePaymentMethod::retrieve($paymentMethodId);
        } catch (ApiErrorException $e) {
            logger()->error("Stripe error retrieving PaymentMethod {$paymentMethodId}: " . $e->getMessage());
            throw new StripeGatewayException("Error obteniendo el método de pago", 500);
        }
    }

    public function getIntentAndCharge(string $paymentIntentId): array
    {
        StripeValidator::validateStripeId($paymentIntentId,'pi','payment intent');
        try {
            $intent = PaymentIntent::retrieve($paymentIntentId, [
                'expand' => ['charges', 'latest_charge'],
            ]);

            if (!$intent) {
                throw new ValidationException("Intent no encontrado en Stripe: {$paymentIntentId}");
            }

            $charge = $intent->charges->data[0] ?? null;
            if (!$charge && isset($intent->latest_charge) && $intent->latest_charge) {
                $charge = Charge::retrieve($intent->latest_charge);
            }

            logger()->info("Intent {$paymentIntentId}: status={$intent->status}, charge_id=" . ($charge->id ?? 'null'));

            return [$intent, $charge];
        } catch (ApiErrorException $e) {
            logger()->error("Stripe error retrieving intent/charge: " . $e->getMessage());
            throw new StripeGatewayException("Error obteniendo los datos", 500);
        }
    }


    public function getStudentPaymentsFromStripe(User $user, ?int $year): array
    {
        $params = [
            'limit' => 100,
            'customer' => $user->stripe_customer_id,
        ];

        if ($year) {
            $params['created'] = [
                'gte' => strtotime("{$year}-01-01 00:00:00"),
                'lte' => strtotime("{$year}-12-31 23:59:59"),
            ];
        }
        try {
            $allSessions = [];
            $lastId = null;
            do {
                if ($lastId) {
                    $params['starting_after'] = $lastId;
                }

                $sessions = Session::all($params);

                $allSessions = array_merge($allSessions, $sessions->data);

                $lastId = end($sessions->data)->id ?? null;

            } while ($lastId && count($sessions->data) === $params['limit']);

            return $allSessions;
        } catch (ApiErrorException $e) {
            logger()->error("Stripe error fetching sessions: " . $e->getMessage());
            throw new StripeGatewayException("Error obteniendo los pagos del estudiante", 500);
        }
    }

    public function getPaymentIntentFromSession(string $sessionId): PaymentIntent
    {
        StripeValidator::validateStripeId($sessionId,'cs','ID de la sesión');
        try {
            $session = Session::retrieve($sessionId);
            if (!$session->payment_intent) {
                throw new ValidationException("Session sin payment_intent: {$sessionId}");
            }

            return PaymentIntent::retrieve($session->payment_intent);
        } catch (ApiErrorException $e) {
            logger()->error("Stripe error retrieving payment intent from session: " . $e->getMessage());
            throw new StripeGatewayException("Error obteniendo los datos", 500);
        }
    }

}
