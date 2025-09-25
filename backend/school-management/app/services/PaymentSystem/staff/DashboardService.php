<?php

namespace App\services\PaymentSystem\staff;

use App\Models\User;
use Ramsey\Uuid\Type\Integer;

class DashboardService{

    public function pendingPaymentAmount(User $user): array
    {
        $conceptosPendientes = $user->paymentConcepts()
        ->whereDoesntHave('payments',fn($q)=>$q->where('user_id',$user->id))
        ->where('status','Activo')
        ->get();

        return [
            'total_monto' => $conceptosPendientes->sum('amount'),
            'total_conceptos' => $conceptosPendientes->count()
        ];

    }

    public function paymentsMade(User $user): float
    {
        return $user->payments()
        ->whereYear('transaction_date',now()->year)
        ->with('paymentConcept')
        ->get()
        ->sum(fn($payment) => $payment->paymentConcept->amount);
    }

    public function overduePayments(User $user)
    {
        return $user->paymentConcepts()
        ->whereDoesntHave('payments',fn($q)=>$q->where('user_id',$user->id))
        ->where('status','Finalizado')
        ->count();
    }

    public function paymentHistory(User $user){
        return $user->payments()
        ->with('paymentConcept:id,concept_name,amount')
        ->orderBy('transaction_date', 'desc')
        ->get()
        ->map(fn($payment)=>[
            'id'=>$payment->id,
            'concepto'=>$payment->paymentConcept->concept_name,
            'monto'=>$payment->paymentConcept->amount,
            'fecha'=>$payment->transaction_date
        ]);

    }

    public function getDashboardData(User $user): array
    {
        return [
            'pendientes' => $this->pendingPaymentAmount($user),
            'realizados' => $this->paymentsMade($user),
            'vencidos'   => $this->overduePayments($user),
            'historial'  => $this->paymentHistory($user),
        ];
    }




}
