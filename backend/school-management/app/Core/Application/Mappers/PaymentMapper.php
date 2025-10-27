<?php

namespace App\Core\Application\Mappers;


use App\Core\Application\DTO\Request\Payment\PaymentCreatedEmailDTO;
use App\Core\Application\DTO\Response\Payment\PaymentDataResponse;
use App\Core\Application\DTO\Response\Payment\PaymentDetailResponse;
use App\Core\Application\DTO\Response\Payment\PaymentHistoryResponse;
use App\Core\Application\DTO\Response\Payment\PaymentListItemResponse;
use App\Core\Application\DTO\Response\Payment\PaymentValidateResponse;
use App\Core\Application\DTO\Response\User\UserDataResponse;
use App\Models\Payment;
use App\Core\Domain\Entities\Payment as DomainPayment;

class PaymentMapper{

    public static function toHistoryResponse(array $payment): PaymentHistoryResponse
    {
        return new PaymentHistoryResponse(
            id: $payment['id'] ?? null,
            concept: $payment['concept_name'] ?? null,
            amount: $payment['amount'] ?? null,
            date: $payment['created_at'] ? date('Y-m-d H:i:s', strtotime($payment['created_at'])): null
        );
    }

    public static function toDetailResponse(Payment $payment): PaymentDetailResponse
    {
        return new PaymentDetailResponse(
            id: $payment->id ?? null,
            concept: $payment->concept_name ?? null,
            amount: $payment->amount ?? null,
            date: $payment->created_at ? $payment->created_at->format('Y-m-d H:i:s'): null,
            status: $payment->status ?? null,
            reference: $payment->payment_intent_id ?? null,
            url: $payment->url ?? null,
            payment_method_details: $payment->payment_method_details ? : null,
        );
    }

    public static function toPaymentDataResponse(DomainPayment $payment): PaymentDataResponse{
        return new PaymentDataResponse(
            id:$payment->id ?? null,
            amount:$payment->amount ?? null,
            status:$payment->status ?? null,
            payment_intent_id:$payment->payment_intent_id ?? null
        );

    }

     public static function toPaymentValidateResponse(UserDataResponse $student, PaymentDataResponse $payment): PaymentValidateResponse
    {
        return new PaymentValidateResponse(
            student: new UserDataResponse(
                id: $student->id ?? null,
                fullName: $student->fullName ?? null,
                email: $student->email ?? null,
                curp: $student->curp ?? null,
                n_control: $student->n_control ?? null
            ),
            payment: new PaymentDataResponse(
                id: $payment->id ?? null,
                amount: $payment->amount ?? null,
                status: $payment->status ?? null,
                payment_intent_id: $payment->payment_intent_id ?? null,
            )
        );
    }

    public static function toListItemResponse(Payment $payment): PaymentListItemResponse
    {
        $type = $payment->payment_method_details['type'] ?? 'desconocido';
        return new PaymentListItemResponse(
            date:$payment->created_at ? $payment->created_at->format('Y-m-d H:i:s'): null,
            concept: $payment->concept_name ?? null,
            amount: $payment->amount ?? null,
            method: $type ?? null,
            fullName: $payment->user->name . ' ' . $payment->user->last_name ?? null,
        );
    }

}
