<?php

namespace App\Services\PaymentSystem;

use App\Jobs\SendMailJob;
use App\Mail\PaymentCreatedMail;
use App\Mail\PaymentFailedMail;
use App\Mail\RequiresActionMail;
use Stripe\Stripe;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Notifications\PaymentCreatedNotification;
use App\Notifications\PaymentFailedNotification;
use App\Notifications\RequiresActionNotification;
use Illuminate\Contracts\Mail\Mailable;
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
        $user = $this->getUserByStripeCustomer($session->customer);

        $payment->update($fields);
        if($payment->status==='paid'){
            $data=[
                'concept_name'=>$payment->paymentConcept->concept_name,
                'amount'=>$payment->paymentConcept->amount,
                'created_at'=>$payment->created_at->format('d/m/Y H:i'),
                'url'=>$payment->url,
                'stripe_session_id'=>$payment->stripe_session_id
            ];
                $mail = new PaymentCreatedMail($data, $user->name, $user->email);
                SendMailJob::dispatch($mail, $user->email);


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
        $user = $this->getUserByStripeCustomer($obj->customer);

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
        $user = $this->getUserByStripeCustomer($obj->customer);
        if (in_array('oxxo', $obj->payment_method_types ?? [])) {
            $oxxo=[
                'amount'=>$obj->amount,
                'voucher'=>$obj->next_action->oxxo_display_details->hosted_voucher_url,
                'reference_number'=>$obj->next_action->oxxo_display_details->number
            ];

                $mail = new RequiresActionMail($oxxo, $user->name, $user->email,'oxxo');
                SendMailJob::dispatch($mail, $user->email);

            return true;
        }

        if (in_array('customer_balance', $obj->payment_method_types ?? [])) {
        $bankTransfer = $obj->next_action->display_bank_transfer_instructions ?? null;

        if ($bankTransfer) {
            $transferData = [
                'amount' => $obj->amount,
                'reference_number' => $bankTransfer->reference_number,
                'bank_name' => $bankTransfer->financial_addresses[0]->sort_code ?? null,
                'clabe' => $bankTransfer->financial_addresses[0]->clabe ?? null,
                'hosted_instructions_url' => $bankTransfer->hosted_instructions_url ?? null,
            ];
            $mail = new RequiresActionMail($transferData, $user->name, $user->email,'bank_transfer');
            SendMailJob::dispatch($mail, $user->email);
            return true;
            }
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

        $user = $this->getUserByStripeCustomer($obj->customer);


        if ($payment && $payment->status !== 'succeeded') {
            logger()->info("Pago fallido eliminado: payment_id={$obj->id}");
            logger()->info("Motivo: {$error}");

                $data=[
                    'concept_name'=>$payment->paymentConcept->concept_name,
                    'amount'=>$payment->paymentConcept->amount
                ];
            $mail = new PaymentFailedMail($data, $user->name, $user->email,$error);
            SendMailJob::dispatch($mail, $user->email);

            $payment->delete();
            return true;
        }
        return false;
    }

    private function getUserByStripeCustomer(string $customerId): User
    {
        $user = User::select('email','name','last_name')
        ->where('stripe_customer_id', $customerId)->first();
        if (!$user) {
            logger()->error("Usuario no encontrado: {$customerId}");
            throw new ModelNotFoundException('Usuario no encontrado');
        }
        return $user;
    }

}
