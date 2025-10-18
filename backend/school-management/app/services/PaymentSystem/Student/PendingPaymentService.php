<?php

namespace App\Services\PaymentSystem\Student;
use App\Models\User;
use Stripe\Stripe;
use App\Models\Payment;
use App\Models\PaymentConcept;
use App\Models\PaymentMethod;
use App\Services\PaymentSystem\StripeService;
use Illuminate\Support\Facades\DB;
use App\Notifications\PaymentCreatedNotification;
use Illuminate\Validation\ValidationException;


class PendingPaymentService{


    public function showPendingPayments(User $user) {
            if($user->status==='baja'){
                throw ValidationException::withMessages(['student'=>"El usuario esta dado de baja"]);
            }

            return PaymentConcept::pendingPaymentConcept($user)
            ->select('id','concept_name','description','amount','start_date','end_date')
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

     public function showOverduePayments(User $user)
    {
            return PaymentConcept::where('status','finalizado')
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


    public function payConcept(User $user, int $conceptId) {
       $session= DB::transaction(function() use ($user, $conceptId) {

            $concept = PaymentConcept::findOrFail($conceptId);

            $stripeService = new StripeService();
            $session = $stripeService->createCheckoutSession($user, $concept);

            Payment::create([
                'user_id' => $user->id,
                'payment_concept_id' => $concept->id,
                'payment_method_id'=>null,
                'payment_intent_id' => null,
                'stripe_payment_method_id' => null,
                'last4' => null,
                'brand' =>  null,
                'voucher_number'=>null,
                'spei_reference'=>null,
                'instructions_url'=>null,
                'type_payment_method' => null,
                'status' => $session->payment_status,
                'url' => $session->url ?? null,
                'stripe_session_id' => $session->id ?? null
            ]);
            return $session->url;

       });
     return $session;

    }

}
