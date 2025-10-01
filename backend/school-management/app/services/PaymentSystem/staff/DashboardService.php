<?php

namespace App\services\PaymentSystem\staff;
use App\Models\PaymentConcept;
use App\Models\Payment;
use App\Models\User;
use App\Utils\ResponseBuilder;

class DashboardService{

    public function pendingPaymentAmount(bool $onlyThisYear = false)
    {
        try{
            $query = PaymentConcept::where('status','Activo')
            ->whereDoesntHave('payments');

            if($onlyThisYear){
                $query->whereYear('created_at',now()->year);
            }

            $conceptosPendientes = $query->get();


            $data = [
                'total_monto' => $conceptosPendientes->sum('amount'),
                'total_conceptos' => $conceptosPendientes->count(),
            ];

            return (new ResponseBuilder())
            ->success(true)
            ->data($data)
            ->build();

        }catch(\Exception $e){
            logger()->error("Error al obtener monto pendiente: " . $e->getMessage());
            return (new ResponseBuilder())
                ->success(false)
                ->message('Error al obtener monto pendiente')
                ->build();
        }



    }


    public function getAllStudents(bool $onlyThisYear = false){
        try{
            $students = User::role('alumno');
            if($onlyThisYear){
                $students->whereYear('created_at',now()->year);
            }

            return (new ResponseBuilder())
            ->success(true)
            ->data($students->get()->count())
            ->build();

        }catch(\Exception $e){
            logger()->error("Error al obtener cantidad de estudiantes: " . $e->getMessage());
            return (new ResponseBuilder())
                ->success(false)
                ->message('Error al obtener cantidad de estudiantes')
                ->build();
        }

    }


    public function paymentsMade(bool $onlyThisYear = false)
    {
        try{
            $query = Payment::with('paymentConcept');
        if($onlyThisYear){
            $query->whereYear('created_at',now()->year);
        }
        $payments =$query->get()
        ->sum(fn($payment) => $payment->paymentConcept->amount);

        return (new ResponseBuilder())
            ->success(true)
            ->data($payments)
            ->build();

        }catch(\Exception $e){
            logger()->error("Error al obtener cantidad de estudiantes: " . $e->getMessage());
            return (new ResponseBuilder())
                ->success(false)
                ->message('Error al obtener cantidad de estudiantes')
                ->build();
        }


    }

    public function getAllConcepts(bool $onlyThisYear = false){

        try{
            $query = PaymentConcept::orderBy('created_at', 'desc');

            if($onlyThisYear){
                $query->whereYear('created_at',now()->year);
            }
            return (new ResponseBuilder())
            ->success(true)
            ->data($query->get())
            ->build();

        }catch(\Exception $e){
            logger()->error("Error al obtener los conceptos: " . $e->getMessage());
            return (new ResponseBuilder())
                ->success(false)
                ->message('Error al obtener los conceptos')
                ->build();
        }

    }

    public function getData(bool $onlyThisYear = false){
        return [
            'ganancias'=> $this->paymentsMade($onlyThisYear),
            'pendientes'=>$this->pendingPaymentAmount($onlyThisYear),
            'alumnos' =>$this->getAllStudents($onlyThisYear)
        ];

    }
}
