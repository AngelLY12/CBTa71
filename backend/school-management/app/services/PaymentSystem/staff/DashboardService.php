<?php

namespace App\services\PaymentSystem\staff;
use App\Models\PaymentConcept;
use App\Models\Payment;
use App\Models\User;

class DashboardService{

    public function pendingPaymentAmount(): array
    {

        $conceptsPendientes = PaymentConcept::where('status', 'Activo')
            ->whereDoesntHave('payments')
            ->get();

        return [
            'total_monto' => $conceptsPendientes->sum('amount'),
            'total_conceptos' => $conceptsPendientes->count(),
        ];

    }


    public function getAllStudents(){
        $students = User::role('alumno')->get();
        return $students->count();
    }


    public function paymentsMadeThisYear(): float
    {
        return Payment::with('paymentConcept')
            ->get()
            ->sum(fn($payment) => $payment->paymentConcept->amount);
    }

}
