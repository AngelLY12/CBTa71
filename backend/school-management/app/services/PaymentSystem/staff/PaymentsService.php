<?php

namespace App\Services\PaymentSystem\Staff;
use App\Models\Payment;
use App\Utils\ResponseBuilder;

class PaymentsService{

    public function showAllPayments(?string $search = null)
    {
           $paymentsQuery = Payment::with('paymentConcept', 'user')
            ->when($search, fn($q) => $q->whereHas('user', fn($sub) =>
                $sub->where('name','like',"%$search%")
                    ->orWhere('last_name','like',"%$search%")
                    ->orWhere('email','like',"%$search%")
            )->orWhereHas('paymentConcept', fn($sub) =>
                $sub->where('concept_name','like',"%$search%")
            ))
            ->select('id','user_id','payment_concept_id','type_payment_method','amount','created_at');

        $paginated = $paymentsQuery->paginate(15);

        $paginated->getCollection()->transform(fn($payment) => [
            'fecha'     => $payment->created_at,
            'concepto'  => $payment->paymentConcept->concept_name,
            'monto'     => $payment->amount,
            'metodo'    => $payment->type_payment_method,
            'nombre'    => $payment->user->name . ' ' . $payment->user->last_name,
        ]);

        return $paginated;


    }
}
