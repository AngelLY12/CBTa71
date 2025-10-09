<?php

namespace App\Services\PaymentSystem\Student;

use App\Models\Payment;
use App\Models\User;
use App\Models\PaymentConcept;
use App\Utils\ResponseBuilder;

class DashboardService{

    public function pendingPaymentAmount(User $user)
    {
            $conceptosPendientes = PaymentConcept::pendingPaymentConcept($user)
            ->select('id', 'amount')
            ->get();

            return [
                'total_monto' => $conceptosPendientes->sum('amount'),
                'total_conceptos' => $conceptosPendientes->count()
            ];

    }


    public function paymentsMade(User $user)
    {
            return Payment::where('payments.user_id', $user->id)
            ->whereYear('payments.created_at',now()->year)
            ->join('payment_concepts', 'payments.payment_concept_id', '=', 'payment_concepts.id')
            ->sum('payment_concepts.amount');
    }

    public function overduePayments(User $user)
    {
            return PaymentConcept::where('status','finalizado')
            ->whereDoesntHave('payments', fn($q) => $q->where('user_id', $user->id))
            ->where(function($q) use ($user) {
                $q->where('is_global', true)
                  ->orWhereHas('users', fn($q) => $q->where('users.id', $user->id))
                  ->orWhereHas('careers', fn($q) => $q->where('careers.id', $user->career_id))
                  ->orWhereHas('paymentConceptSemesters', fn($q) => $q->where('semestre', $user->semestre));
            })
            ->count();
    }

    public function paymentHistory(User $user){

           return $user->payments()
            ->with('paymentConcept:id,concept_name,amount')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($payment)=>[
                'id'=>$payment->id,
                'concepto'=>$payment->paymentConcept->concept_name,
                'monto'=>$payment->paymentConcept->amount,
                'fecha'=>$payment->created_at
            ]);

    }

    public function getDashboardData(User $user)
    {
        return [
            'pendientes' => $this->pendingPaymentAmount($user),
            'realizados' => $this->paymentsMade($user),
            'vencidos'   => $this->overduePayments($user),
        ];
    }




}
