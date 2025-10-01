<?php

namespace App\Services\PaymentSystem\Student;

use App\Models\User;
use App\Models\PaymentConcept;
use App\Utils\ResponseBuilder;

class DashboardService{

    public function pendingPaymentAmount(User $user)
    {
        try{
            $conceptosPendientes = PaymentConcept::where('status','Activo')
            ->whereDoesntHave('payments', fn($q) => $q->where('user_id', $user->id))
            ->where(function($q) use ($user) {
                $q->where('is_global', true)
                ->orWhereHas('users', fn($q) => $q->where('users.id', $user->id))
                ->orWhereHas('careers', fn($q) => $q->where('careers.id', $user->career_id))
                ->orWhereHas('paymentConceptSemesters', fn($q) => $q->where('semestre', $user->semestre));
            })
            ->get();

            $data = [
                'total_monto' => $conceptosPendientes->sum('amount'),
                'total_conceptos' => $conceptosPendientes->count()
            ];
        return (new ResponseBuilder())->success(true)
        ->data($data)
        ->build();

        }catch (\Exception $e) {
            return (new ResponseBuilder())->success(false)
                                         ->message('Ocurrió un error al obtener el monto pendiente')
                                         ->build();
        }

    }


    public function paymentsMade(User $user)
    {
        try{
            $payments =$user->payments()
            ->whereYear('created_at',now()->year)
            ->with('paymentConcept')
            ->get()
            ->sum(fn($payment) => $payment->paymentConcept->amount);

            return (new ResponseBuilder())->success(true)
            ->data($payments)
            ->build();


        }catch (\Exception $e) {
            return (new ResponseBuilder())->success(false)
                                         ->message('Ocurrió un error al obtener el monto')
                                         ->build();
        }

    }

    public function overduePayments(User $user)
    {
        try{
            $pagosAtrasados = PaymentConcept::where('status','Finalizado')
            ->whereDoesntHave('payments', fn($q) => $q->where('user_id', $user->id))
            ->where(function($q) use ($user) {
                $q->where('is_global', true)
                  ->orWhereHas('users', fn($q) => $q->where('users.id', $user->id))
                  ->orWhereHas('careers', fn($q) => $q->where('careers.id', $user->career_id))
                  ->orWhereHas('paymentConceptSemesters', fn($q) => $q->where('semestre', $user->semestre));
            })
            ->count();
            return (new ResponseBuilder())->success(true)
            ->data($pagosAtrasados)
            ->build();

        }catch (\Exception $e) {
            return (new ResponseBuilder())->success(false)
                                         ->message('Ocurrió un error al obtener los conceptos atrasados')
                                         ->build();
        }

    }

    public function paymentHistory(User $user){

        try{
            $historial=$user->payments()
            ->with('paymentConcept:id,concept_name,amount')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($payment)=>[
                'id'=>$payment->id,
                'concepto'=>$payment->paymentConcept->concept_name,
                'monto'=>$payment->paymentConcept->amount,
                'fecha'=>$payment->created_at
            ]);

            if($historial->isEmpty()){

            }

            return (new ResponseBuilder())->success(true)
            ->data($historial)
            ->build();

        }catch (\Exception $e) {
            return (new ResponseBuilder())->success(false)
                                         ->message('Ocurrió un error al obtener el historial')
                                         ->build();
        }



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
