<?php

namespace App\Core\Infraestructure\Mappers;

use App\Models\Payment;
use App\Core\Domain\Entities\Payment as DomainPayment;

class PaymentMapper{

    public static function toDomain(Payment $payment):DomainPayment
    {
        return new DomainPayment(
            id: $payment->id,
            user_id:$payment->user_id,
            payment_concept_id:$payment->payment_concept_id,
            payment_method_id:$payment->payment_method_id,
            stripe_payment_method_id:$payment->stripe_payment_method_id,
            concept_name:$payment->concept_name,
            amount:$payment->amount,
            payment_method_details:$payment->payment_method_details,
            status:$payment->status,
            payment_intent_id:$payment->payment_intent_id,
            url:$payment->url,
            stripe_session_id:$payment->stripe_session_id
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
            'payment_method_details'=>$payment->payment_method_details,
            'status' => $payment->status,
            'payment_intent_id' => $payment->payment_intent_id,
            'url' => $payment->url,
            'stripe_session_id' => $payment->stripe_session_id,
        ];
    }

}
