<?php

namespace App\Services\PaymentSystem;
use Stripe\Stripe;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Notifications\PaymentCreatedNotification;
use App\Notifications\PaymentFailedNotification;
use App\Notifications\RequiresActionNotification;
use Illuminate\Database\Eloquent\ModelNotFoundException;


class WebhookService{

     public function __construct(){
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function sessionCompleted($obj)
    {
        if($obj->mode==='payment'){
            return $this->handlePaymentSession($obj, [
                'payment_intent_id' => $obj->payment_intent,
                'status' => $obj->payment_status,
            ]);

        }
        logger()->info("Sesión setup ignorada en webhook. session_id={$obj->id}");
        return true;
    }

    public function sessionAsync($obj) {
        return $this->handlePaymentSession($obj, [
        'status' => $obj->payment_status,
        ]);

    }

    private function handlePaymentSession($session, array $fields)
    {
        try {
            $payment = Payment::where('stripe_session_id', $session->id)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            logger()->warning("No se encontró el pago con session_id={$session->id}");
            throw $e;
        }
        $payment->update($fields);
        if($payment->status==='paid'){
            try {
                $payment->user->notify(new PaymentCreatedNotification($payment));

            } catch (\Exception $e) {
                logger()->error("Error al notificar al usuario: " . $e->getMessage());
            }
        }

        return $payment;
    }

    public function paymentMethodAttached($obj){

        if (!$obj) {
            logger()->error("PaymentMethod no encontrado: {$obj->id}");
            throw new \InvalidArgumentException('El PaymentMethod es nulo.');
        }
        $paymentMethodId = $obj->id;

        if (PaymentMethod::where('stripe_payment_method_id', $paymentMethodId)->exists()) {
            logger()->info("El método de pago {$paymentMethodId} ya existe");
            return false;
        }
        $user = User::where('stripe_customer_id', $obj->customer)->firstOrFail();

        PaymentMethod::create([
            'user_id' => $user->id,
            'stripe_payment_method_id' => $paymentMethodId,
            'brand' => $obj->card->brand,
            'last4' => $obj->card->last4,
            'exp_month' => $obj->card->exp_month,
            'exp_year' => $obj->card->exp_year,
        ]);
        return true;


    }

    public function requiresAction($obj){
        $user = User::where('stripe_customer_id', $obj->customer)->first();
        if (!$user) {
             logger()->error("Usuario no encontrado: {$obj->customer}");
            throw new ModelNotFoundException('Usuario no encontrado para requiresAction');
        }
        if (in_array('oxxo', $obj->payment_method_types ?? [])) {
            $oxxo=[
                'amount'=>$obj->amount,
                'voucher'=>$obj->next_action->oxxo_display_details->hosted_voucher_url,
                'reference_number'=>$obj->next_action->oxxo_display_details->number
            ];

            try {
                $user->notify((new RequiresActionNotification($oxxo))->delay(now()->addSeconds(5)));
            } catch (\Exception $e) {
                logger()->error("Error al notificar al usuario: " . $e->getMessage());
            }
            return true;
        }
        return false;
    }

    public function handleFailedOrExpiredPayment($obj, string $eventType)
    {
        $payment = null;
        $error = null;

        if (in_array($eventType, ['payment_intent.payment_failed', 'payment_intent.canceled'])) {
            $payment = Payment::where('payment_intent_id', $obj->id)->first();
            $error = $obj->last_payment_error->message ?? 'Error desconocido';
        } elseif ($eventType === 'checkout.session.expired') {
            $payment = Payment::where('stripe_session_id', $obj->id)->first();
            $error = "La sesión de pago expiró";
        }

        if ($payment && $payment->status !== 'succeeded') {
            logger()->info("Pago fallido eliminado: payment_id={$obj->id}");
            logger()->info("Motivo: {$error}");

            try {
                $payment->user->notify(new PaymentFailedNotification($payment, $error));
            } catch (\Exception $e) {
                logger()->error("Error al notificar al usuario: " . $e->getMessage());
            }

            $payment->delete();
            return true;
        }
        return false;
    }
}
