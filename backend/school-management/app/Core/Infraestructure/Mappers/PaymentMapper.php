<?php

namespace App\Core\Infraestructure\Mappers;

use App\Core\Application\DTO\Payment\PaymentCreatedEmailDTO;
use App\Core\Application\DTO\Payment\PaymentDataDTO;
use App\Core\Application\DTO\Payment\PaymentDetailDTO;
use App\Core\Application\DTO\Payment\PaymentDTO;
use App\Core\Application\DTO\Payment\PaymentHistoryDTO;
use App\Core\Application\DTO\Payment\PaymentListItemDTO;
use App\Core\Application\DTO\Payment\PaymentValidateDTO;
use App\Core\Application\DTO\Payment\PaymentWithConceptDTO;
use App\Core\Application\DTO\User\UserDataDTO;
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
