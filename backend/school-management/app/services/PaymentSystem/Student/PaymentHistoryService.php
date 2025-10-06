<?php

namespace App\Services\PaymentSystem\Student;

use App\Models\User;
use App\Utils\ResponseBuilder;



class PaymentHistoryService{

    public function paymentHistory(User $user)
{
        return $user->payments()
            ->with('paymentConcept:id,concept_name,description,amount')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($payment) => [
                'id'          => $payment->id,
                'concepto'    => $payment->paymentConcept->concept_name ?? null,
                'descripcion' => $payment->paymentConcept->description ?? null,
                'monto'       => $payment->paymentConcept->amount ?? null,
                'fecha'       => $payment->created_at,
                'estatus'     => $payment->status,
                'referencia'  => $payment->payment_intent_id,
                'url'         => $payment->url ?? null,
                'tarjeta'     => $payment->last4 && $payment->brand ? [
                    'brand' => $payment->brand,
                    'last4' => $payment->last4,
                ] : null,
                'tipo_metodo'=>$payment->type_payment_method,
                'oxxo' => $payment->voucher_number??null,
                'transferencia' =>$payment->spei_reference && $payment->instructions_url ? [
                    'referencia' => $payment->spei_reference,
                    'instrucciones'=>$payment->instructions_url
                ]:null
            ]);

}

}
