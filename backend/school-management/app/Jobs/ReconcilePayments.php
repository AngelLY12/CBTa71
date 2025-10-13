<?php

namespace App\Jobs;


use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Notifications\PaymentValidatedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Stripe\StripeClient;
class ReconcilePayments implements ShouldQueue
{
      use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected StripeClient $stripe;
    public $tries = 3;
    public $backoff = [10, 30, 60];

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));

    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Payment::where('status', 'paid')
            ->where('created_at', '>=', now()->subMonths(1))
            ->chunk(50, function ($payments) {
                foreach ($payments as $payment) {
                    try {
                        $pi = $this->stripe->paymentIntents->retrieve($payment->payment_intent_id);
                        $charge = $pi->charges->data[0] ?? null;

                        if ($charge) {
                            $savedPaymentMethod = PaymentMethod::where('stripe_payment_method_id', $charge->payment_method)->first();

                            $payment->update([
                                'payment_method_id' => $savedPaymentMethod?->id,
                                'stripe_payment_method_id' => $charge?->payment_method,
                                'status' => $pi->status,
                                'last4' => $charge?->payment_method_details?->card?->last4,
                                'brand' => $charge?->payment_method_details?->card?->brand,
                                'voucher_number' => $charge?->payment_method_details?->oxxo?->number,
                                'spei_reference' => $charge?->payment_method_details?->bank_transfer?->reference_number,
                                'instructions_url' => $pi->next_action?->display_bank_transfer_instructions?->hosted_instructions_url,
                                'url' => $charge?->receipt_url ?? $payment->url,
                            ]);

                            try {
                                $payment->user->notify(new PaymentValidatedNotification($payment));
                            } catch (\Exception $e) {
                                logger()->error("Error al notificar al usuario: " . $e->getMessage());
                            }
                        }

                    } catch (\Exception $e) {
                        logger()->error("Error al reconciliar el pago {$payment->id} (pi={$payment->payment_intent_id}, user={$payment->user_id}): " . $e->getMessage());
                    }
                }
            });

    }
}
