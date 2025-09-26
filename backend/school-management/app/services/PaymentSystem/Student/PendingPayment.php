<?php

namespace App\Services\PaymentSystem\Student;
use App\Models\User;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use App\Models\Payment;
use App\Models\PaymentConcept;

class PendingPayment{


    public function showPendingPayments(User $user) {
        return PaymentConcept::where('status', 'Activo')
            ->whereDoesntHave('payments', fn($q) => $q->where('user_id', $user->id))
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


    public function payConcept(User $user,int $conceptId, string $paymentMethodId){
        Stripe::setApiKey(config('services.stripe.secret'));

        $concept = PaymentConcept::findOrFail($conceptId);

        $paymentIntent = PaymentIntent::create([
            'amount'=>intval($concept->amount*100),
            'currency'=>'mxn',
            'customer'=>$user->stripe_customer_id,
            'payment_method'=>$paymentMethodId,
            'off_session'=>true,
            'confirm'=>true

        ]);

        $payment = Payment::create([
            'user_id' => $user->id,
            'payment_concept_id' => $concept->id,
            'payment_intent_id' => $paymentIntent->id,
            'payment_method_id' => $paymentMethodId,
            'status' => $paymentIntent->status,
            'transaction_date'=>now(),
            'url'=> $paymentIntent->charges->data[0]->receipt_url ?? null
        ]);

        return $payment;


    }

}
