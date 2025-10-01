<?php

namespace App\Services\PaymentSystem\Student;

use App\Models\User;
use App\Utils\ResponseBuilder;



class PaymentHistoryService{

    public function paymentHistory(User $user)
{
    try {
        $payments = $user->payments()
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
                'url'         => $payment->url ?? null,
                'tarjeta'     => $payment->last4 && $payment->brand ? [
                    'brand' => $payment->brand,
                    'last4' => $payment->last4,
                ] : null,
            ]);

        if ($payments->isEmpty()) {
                return (new ResponseBuilder())->success(false)
                                             ->message('No hay pagos en el historial')
                                             ->build();
        }

        return (new ResponseBuilder())
            ->success(true)
            ->data($payments)
            ->build();

    } catch (\Exception $e) {
        logger()->error("Error fetching payment history for user {$user->id}: " . $e->getMessage());

        return (new ResponseBuilder())
            ->success(false)
            ->message('No se pudo obtener el historial de pagos. Intenta más tarde.')
            ->build();
    }
}

}
