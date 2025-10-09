<?php

namespace App\Jobs;


use App\Models\Payment;
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
        $pendingPayments = Payment::where('status', 'pending')
        ->where('created_at', '>=', now()->subMonths(6))
        ->get();

        foreach ($pendingPayments as $payment) {
            try {
                $pi = $this->stripe->paymentIntents->retrieve($payment->payment_intent_id);

                if ($pi) {
                    $charge = $pi->charges->data[0] ?? null;
                    $payment->update([
                        'status' => $pi->status,
                        'last4' => $charge?->payment_method_details?->card?->last4,
                        'brand' => $charge?->payment_method_details?->card?->brand,
                        'voucher_number' => $charge?->payment_method_details?->oxxo?->number,
                        'spei_reference' => $charge?->payment_method_details?->bank_transfer?->reference_number,
                        'instructions_url' => $pi->next_action?->display_bank_transfer_instructions?->hosted_instructions_url,
                        'url' => $charge?->receipt_url ?? $payment->url,
                    ]);
                }

            } catch (\Exception $e) {
                logger()->error("Error al reconciliar el pago {$payment->id}: " . $e->getMessage());
            }
        }
    }
}
