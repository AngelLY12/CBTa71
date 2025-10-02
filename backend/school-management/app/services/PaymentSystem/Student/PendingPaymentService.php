<?php

namespace App\Services\PaymentSystem\Student;
use App\Models\User;
use Stripe\Stripe;
use App\Models\Payment;
use App\Models\PaymentConcept;
use App\Services\PaymentSystem\StripeService;
use Illuminate\Support\Facades\DB;


class PendingPaymentService{


    public function showPendingPayments(User $user) {

            return PaymentConcept::where('status', 'Activo')
                ->whereDoesntHave('payments', fn($q) => $q->where('user_id', $user->id))
                ->whereDate('start_date', '<=', now())
                ->whereDate('end_date', '>=', now())
                ->where(function($q) use ($user) {
                    $q->where('is_global', true)
                      ->orWhereHas('users', fn($q) => $q->where('users.id', $user->id))
                      ->orWhereHas('careers', fn($q) => $q->where('careers.id', $user->career_id))
                      ->orWhereHas('paymentConceptSemesters', fn($q) => $q->where('semestre', $user->semestre));
                })
                ->get()
                ->map(fn($concept) => [
                    'id'           => $concept->id,
                    'concepto'     => $concept->concept_name,
                    'descripcion'  => $concept->description,
                    'monto'        => $concept->amount,
                    'fecha_inicio' => $concept->start_date,
                    'fecha_fin'    => $concept->end_date,
                ]);


    }


    public function payConcept(User $user, int $conceptId, ?string $paymentMethodId=null,  string $paymentType) {
       return DB::transaction(function() use ($user, $conceptId, $paymentMethodId, $paymentType) {

            $concept = PaymentConcept::findOrFail($conceptId);
            $stripeService = new StripeService();
            $paymentIntent = $stripeService->createPaymentIntent($user, $concept, $paymentMethodId,$paymentType);

            $charge = $paymentIntent->charges->data[0] ?? null;

            return Payment::create([
                'user_id' => $user->id,
                'payment_concept_id' => $concept->id,
                'payment_intent_id' => $paymentIntent->id,
                'stripe_payment_method_id' => $paymentMethodId,
                'last4' => $charge?->payment_method_details?->card?->last4 ?? null,
                'brand' =>  $charge?->payment_method_details?->card?->brand ?? null,
                'type_payment_method' => $paymentType,
                'status' => $paymentIntent->status,
                'url' => $charge->receipt_url
                    ?? $paymentIntent->next_action?->display_bank_transfer_instructions?->hosted_instructions_url
                    ?? null
            ]);

       });
    }

}
