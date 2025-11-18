<?php

namespace App\Core\Application\Mappers;

use App\Core\Application\DTO\Request\Mail\NewPaymentConceptEmailDTO;
use App\Core\Application\DTO\Request\Mail\NewUserCreatedEmailDTO;
use App\Core\Application\DTO\Request\Mail\PaymentCreatedEmailDTO;
use App\Core\Application\DTO\Request\Mail\PaymentFailedEmailDTO;
use App\Core\Application\DTO\Request\Mail\PaymentValidatedEmailDTO;
use App\Core\Application\DTO\Request\Mail\RequiresActionEmailDTO;
use App\Core\Domain\Entities\Payment;
use Carbon\Carbon;

class MailMapper
{
    public static function toPaymentCreatedEmailDTO(Payment $payment, string $recipientName, string $recipientEmail): PaymentCreatedEmailDTO
    {
        return new PaymentCreatedEmailDTO(
            recipientName: $recipientName,
            recipientEmail: $recipientEmail,
            concept_name: $payment->concept_name,
            amount: $payment->amount,
            created_at: Carbon::now()->toDateTimeString(),
            url: $payment->url,
            stripe_session_id: $payment->stripe_session_id
        );
    }

    public static function toNewPaymentConceptEmailDTO(array $data): NewPaymentConceptEmailDTO
    {
        return new NewPaymentConceptEmailDTO(
            recipientName:$data['recipientName'],
            recipientEmail:$data['recipientEmail'],
            concept_name:$data['concept_name'],
            amount:$data['amount'],
            end_date:$data['end_date']
        );
    }

    public static function toPaymentValidatedEmailDTO(array $data): PaymentValidatedEmailDTO
    {
        return new PaymentValidatedEmailDTO(
            recipientName: $data['recipientName'],
            recipientEmail: $data['recipientEmail'],
            concept_name: $data['concept_name'],
            amount: $data['amount'],
            payment_method_detail: $data['payment_method_detail'],
            payment_intent_id: $data['payment_intent_id'],
            url:$data['url']
        );
    }

    public static function toPaymentFailedEmailDTO(array $data): PaymentFailedEmailDTO
    {
        return new PaymentFailedEmailDTO(
            recipientName: $data['recipientName'],
            recipientEmail: $data['recipientEmail'],
            concept_name: $data['concept_name'],
            amount:$data['amount'],
            error:$data['error']
        );
    }

    public static function toRequiresActionEmailDTO(array $data): RequiresActionEmailDTO
    {
        return new RequiresActionEmailDTO(
            recipientName: $data['recipientName'],
            recipientEmail: $data['recipientEmail'],
            concept_name: $data['concept_name'],
            amount: $data['amount'],
            next_action: $data['next_action'],
            payment_method_options: $data['payment_method_options']
        );
    }
    public static function toNewUserCreatedEmailDTO(array $data): NewUserCreatedEmailDTO
    {
        return new NewUserCreatedEmailDTO(
            recipientName: $data['recipientName'],
            recipientEmail:$data['recipientEmail'],
            password:$data['password']
        );
    }

}
