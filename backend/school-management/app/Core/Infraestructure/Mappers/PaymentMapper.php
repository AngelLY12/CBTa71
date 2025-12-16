<?php

namespace App\Core\Infraestructure\Mappers;

use App\Models\Payment;
use App\Core\Domain\Entities\Payment as DomainPayment;

class PaymentMapper{

    public static function toDomain(Payment $payment):DomainPayment
    {
        return new DomainPayment(
            id: $payment->id ?? null,
            user_id:$payment->user_id,
            payment_concept_id:$payment->payment_concept_id ?? null,
            payment_method_id:$payment->payment_method_id ?? null,
            stripe_payment_method_id:$payment->stripe_payment_method_id ?? null,
            concept_name:$payment->concept_name ?? null,
            amount:$payment->amount ?? null,
            amount_received: $payment->amount_received ?? null,
            payment_method_details:$payment->payment_method_details ?? [],
            status:$payment->status,
            payment_intent_id:$payment->payment_intent_id ?? null,
            url:$payment->url ?? null,
            stripe_session_id:$payment->stripe_session_id ?? null,
        );
    }

    public static function toPersistence(DomainPayment $payment): array
    {
        return [
            'user_id' => $payment->user_id,
            'payment_concept_id' => $payment->payment_concept_id,
            'payment_method_id' => $payment->payment_method_id,
            'stripe_payment_method_id' => $payment->stripe_payment_method_id,
            'concept_name'=>$payment->concept_name,
            'amount'=>$payment->amount,
            'amount_received'=>$payment->amount_received,
            'payment_method_details'=>$payment->payment_method_details,
            'status' => $payment->status,
            'payment_intent_id' => $payment->payment_intent_id,
            'url' => $payment->url,
            'stripe_session_id' => $payment->stripe_session_id,
        ];
    }

}
