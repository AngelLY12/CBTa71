<?php

namespace App\Core\Application\Mappers;


use App\Core\Application\DTO\Response\Payment\PaymentDataResponse;
use App\Core\Application\DTO\Response\Payment\PaymentDetailResponse;
use App\Core\Application\DTO\Response\Payment\PaymentHistoryResponse;
use App\Core\Application\DTO\Response\Payment\PaymentListItemResponse;
use App\Core\Application\DTO\Response\Payment\PaymentValidateResponse;
use App\Core\Application\DTO\Response\User\UserDataResponse;
use App\Core\Domain\Entities\PaymentConcept;
use App\Models\Payment;
use App\Core\Domain\Entities\Payment as DomainPayment;
use Stripe\Checkout\Session;

class PaymentMapper{

    public static function toDomain(PaymentConcept $concept, int $userId, Session $session): DomainPayment
    {
        return new DomainPayment(
            id: null,
            user_id: $userId,
            payment_concept_id: $concept->id,
            payment_method_id: null,
            stripe_payment_method_id: null,
            concept_name: $concept->concept_name,
            amount: $concept->amount,
            amount_received: null,
            payment_method_details: [],
            status: EnumMapper::fromStripe($session->payment_status),
            payment_intent_id: null,
            url: $session->url ?? null,
            stripe_session_id: $session->id ?? null
        );
    }

    public static function toHistoryResponse(array $payment): PaymentHistoryResponse
    {
        return new PaymentHistoryResponse(
            id: $payment['id'] ?? null,
            concept: $payment['concept_name'] ?? null,
            amount: $payment['amount'] ?? null,
            amount_received: $payment['amount_received'] ?? null,
            date: $payment['created_at'] ? date('Y-m-d H:i:s', strtotime($payment['created_at'])): null
        );
    }

    public static function toDetailResponse(Payment $payment): PaymentDetailResponse
    {
        $domainPayment= $payment->toDomain();
        return new PaymentDetailResponse(
            id: $payment->id ?? null,
            concept: $payment->concept_name ?? null,
            amount: $payment->amount ?? null,
            amount_received: $payment->amount_received ?? null,
            balance: $domainPayment->isOverPaid()? $domainPayment->getOverPaidAmount() : null,
            date: $payment->created_at ? $payment->created_at->format('Y-m-d H:i:s'): null,
            status: $payment->status->value ?? null,
            reference: $payment->payment_intent_id ?? null,
            url: $payment->url ?? null,
            payment_method_details: $payment->payment_method_details ? : null,
        );
    }

    public static function toPaymentDataResponse(DomainPayment $payment): PaymentDataResponse{
        return new PaymentDataResponse(
            id:$payment->id ?? null,
            amount:$payment->amount ?? null,
            amount_received: $payment->amount_received ?? null,
            status:$payment->status->value ?? null,
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
                amount_received: $payment->amount_received ?? null,
                status: $payment->status->value ?? null,
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
            amount_received: $payment->amount_received ?? null,
            method: $type ?? null,
            fullName: $payment->user->name . ' ' . $payment->user->last_name ?? null,
        );
    }

}
