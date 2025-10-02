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

            if ($event->type === 'payment_intent.succeeded') {
                $paymentIntent = $event->data->object;
                $this->updatePaymentStatus($paymentIntent->id, 'succeeded');
            }

            if ($event->type === 'payment_intent.payment_failed') {
                $paymentIntent = $event->data->object;
                $this->updatePaymentStatus($paymentIntent->id, 'failed');
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
