<?php

namespace App\Http\Controllers\Students;

use Illuminate\Http\Request;
use App\Models\Payment;
use Stripe\Webhook;
use App\Http\Controllers\Controller;


class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('services.stripe.webhook');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
            $obj = $event->data->object;

            if ($event->type === 'checkout.session.completed') {
                if (!empty($obj->mode) && $obj->mode === 'setup') {
                    $setupIntentId = $obj->setup_intent ?? null;
                    if ($setupIntentId) {
                        $setupIntent = \Stripe\SetupIntent::retrieve($setupIntentId);
                        logger()->info("Setup completed: customer={$obj->customer}, pm={$setupIntent->payment_method}");
                    }
                }
                if(!empty($obj->mode) && $obj->mode === 'payment'){
                    $session = $obj;
                    $payment = Payment::where('stripe_session_id', $session->id)->first();
                    $paymentIntent = \Stripe\PaymentIntent::retrieve($session->payment_intent);
                    $charge = $paymentIntent->charges->data[0];
                    if(!$charge){
                        logger()->warning("PaymentIntent {$paymentIntent->id} no tiene charge.");
                        return;
                    }

                    $type = $charge->payment_method_details->type ?? null;

                    $last4 = null;
                    $brand = null;
                    $voucher= null;
                    $spei=null;
                    $instructions=null;

                    switch($type){
                        case 'card':
                            $last4 = $charge->payment_method_details->card->last4 ?? null;
                            $brand = $charge->payment_method_details->card->brand ?? null;
                        break;
                        case 'bank_transfer':
                            $spei=$charge->payment_method_details->bank_transfer->reference_number ?? null;
                            $instructions=$paymentIntent->next_action->display_bank_transfer_instructions->hosted_instructions_url ?? null;
                        break;
                        case 'oxxo':
                            $voucher=$charge->payment_method_details->oxxo->number ?? null;
                        break;


                    }
                    $payment->update([
                        'payment_intent_id' => $paymentIntent->id,
                        'stripe_payment_method_id' => $charge->payment_method,
                        'last4' => $last4,
                        'brand' => $brand,
                        'voucher_number'=>$voucher,
                        'spei_reference'=>$spei,
                        'instructions_url'=>$instructions,
                        'type_payment_method' => $charge->payment_method_details->type ?? null,
                        'status' => $paymentIntent->status,
                        'url' => $charge->receipt_url ?? $payment->url
                    ]);
                }
            }

            if ($event->type === 'payment_intent.payment_failed') {
                $pi = $obj;
                $payment = Payment::where('payment_intent_id', $pi->id)->first();
                if ($payment) {
                    $payment->status = 'failed';
                    $payment->save();
                }
            }
            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            logger()->error('Stripe Webhook Error: ' . $e->getMessage());
            return response()->json(['success' => false], 400);
        }
    }

    private function updatePaymentStatus($paymentIntentObject)
    {
        $payment = Payment::where('payment_intent_id', $paymentIntentObject->id)->first();

        if ($payment) {
            $payment->status = $paymentIntentObject->status;

            $charge = $paymentIntentObject->charges->data[0] ?? null;

            $payment->url = $charge->receipt_url
                ?? $paymentIntentObject->next_action?->display_bank_transfer_instructions?->hosted_instructions_url
                ?? $payment->url;

            $payment->save();
        }
    }

}
