<?php

namespace App\services\PaymentSystem\staff;
use App\Models\PaymentConcept;
use App\Models\Payment;
use App\Models\User;
use App\Utils\ResponseBuilder;

class DashboardService{

    public function pendingPaymentAmount(bool $onlyThisYear = false)
    {
            $query = PaymentConcept::where('status','Activo')
            ->whereDoesntHave('payments');

            if($onlyThisYear){
                $query->whereYear('created_at',now()->year);
            }

            $conceptosPendientes = $query->get();


            return [
                'total_monto' => $conceptosPendientes->sum('amount'),
                'total_conceptos' => $conceptosPendientes->count(),
            ];


    }


    public function getAllStudents(bool $onlyThisYear = false){
            $students = User::role('alumno');
            if($onlyThisYear){
                $students->whereYear('created_at',now()->year);
            }
            return $students->get()->count();

    }


    public function paymentsMade(bool $onlyThisYear = false)
    {
            $query = Payment::with('paymentConcept');
        if($onlyThisYear){
            $query->whereYear('created_at',now()->year);
        }
        $payments =$query->get()
        ->sum(fn($payment) => $payment->paymentConcept->amount);

        return $payments;


    }

    public function getAllConcepts(bool $onlyThisYear = false){

            $query = PaymentConcept::orderBy('created_at', 'desc');

            if($onlyThisYear){
                $query->whereYear('created_at',now()->year);
            }
            return $query->get();


    }

    public function getData(bool $onlyThisYear = false){
        return [
            'ganancias'=> $this->paymentsMade($onlyThisYear),
            'pendientes'=>$this->pendingPaymentAmount($onlyThisYear),
            'alumnos' =>$this->getAllStudents($onlyThisYear)
        ];

    }
}
