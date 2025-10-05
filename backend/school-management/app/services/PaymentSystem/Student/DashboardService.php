<?php

namespace App\Services\PaymentSystem\Student;

use App\Models\User;
use App\Models\PaymentConcept;
use App\Utils\ResponseBuilder;

class DashboardService{

    public function pendingPaymentAmount(User $user)
    {
            $conceptosPendientes = PaymentConcept::where('status','Activo')
            ->whereDoesntHave('payments', fn($q) => $q->where('user_id', $user->id))
            ->where(function($q) use ($user) {
                $q->where('is_global', true)
                ->orWhereHas('users', fn($q) => $q->where('users.id', $user->id))
                ->orWhereHas('careers', fn($q) => $q->where('careers.id', $user->career_id))
                ->orWhereHas('paymentConceptSemesters', fn($q) => $q->where('semestre', $user->semestre));
            });

            return [
                'total_monto' => $conceptosPendientes->sum('amount'),
                'total_conceptos' => $conceptosPendientes->count()
            ];

    }


    public function paymentsMade(User $user)
    {
            return $user->payments()
            ->whereYear('created_at',now()->year)
            ->with('paymentConcept')
            ->sum(fn($payment) => $payment->paymentConcept->amount);
    }

    public function overduePayments(User $user)
    {
            return PaymentConcept::where('status','Finalizado')
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
