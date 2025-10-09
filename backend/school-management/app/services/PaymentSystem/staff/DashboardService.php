<?php

namespace App\Services\PaymentSystem\Staff;
use App\Models\PaymentConcept;
use App\Models\Payment;
use App\Models\User;
use App\Utils\ResponseBuilder;

class DashboardService{

    public function pendingPaymentAmount(bool $onlyThisYear = false)
    {
            $query = PaymentConcept::pendingPaymentConcept();

            if($onlyThisYear){
                $query->whereYear('created_at',now()->year);
            }

            return [
                'total_monto' => $query->sum('amount'),
                'total_conceptos' => $query->count(),
            ];


    }


    public function getAllStudents(bool $onlyThisYear = false){
            $students = User::role('student')->where('status','activo');
            if($onlyThisYear){
                $students->whereYear('created_at',now()->year);
            }
            return $students->count();

    }


    public function paymentsMade(bool $onlyThisYear = false)
    {
        $query = Payment::join('payment_concepts', 'payments.payment_concept_id', '=', 'payment_concepts.id');

        if ($onlyThisYear) {
            $query->whereYear('payments.created_at', now()->year);
        }

        return $query->sum('payment_concepts.amount');

    }

    public function getAllConcepts(bool $onlyThisYear = false){

            $query = PaymentConcept::select(
                'id',
                'concept_name',
                'status',
                'start_date',
                'end_date',
                'amount')
            ->orderBy('created_at', 'desc');

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
