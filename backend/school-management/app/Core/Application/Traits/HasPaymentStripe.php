<?php

namespace App\Core\Application\Traits;

use App\Core\Application\Mappers\EnumMapper;
use App\Core\Domain\Entities\Payment;
use App\Core\Domain\Entities\PaymentMethod;
use App\Core\Domain\Enum\Payment\PaymentStatus;
use App\Core\Domain\Repositories\Command\Payments\PaymentRepInterface;
use App\Core\Domain\Repositories\Query\User\UserQueryRepInterface;

trait HasPaymentStripe
{

    private PaymentRepInterface $repo;
    public function setRepository(PaymentRepInterface $repo): void
    {
        $this->repo = $repo;
    }
    public function updatePaymentWithStripeData(Payment $payment, $pi, $charge, PaymentMethod $savedPaymentMethod): Payment
    {
        $expected = $pi->amount / 100;
        $received = ($pi->amount_received ?? 0) / 100;
        $internalStatus = $this->verifyStatus($pi, $received, $expected);
        $paymentMethodDetails = $this->formatPaymentMethodDetails($charge->payment_method_details);
        $fields=[
            'amount_received' => number_format($received, 2, '.', ''),
            'payment_method_id' => $savedPaymentMethod?->id,
            'stripe_payment_method_id' => $charge?->payment_method,
            'status' => $internalStatus,
            'payment_method_details'=>$paymentMethodDetails,
            'url' => $charge?->receipt_url ?? $payment->url,
        ];
        $newPayment=$this->repo->update($payment->id, $fields);
        logger()->info("Pago {$payment->id} actualizado correctamente.");
        return $newPayment;
    }
    public function formatPaymentMethodDetails($details): array
    {
        if ($details->type === 'card' && isset($details->card)) {
            return [
                'type' => $details->type,
                'brand' => $details->card->brand,
                'last4' => $details->card->last4,
                'funding' => $details->card->funding,
            ];
        }

        return (array) $details;
    }

    private function verifyStatus($pi, string|float $received, string|float $expected): PaymentStatus
    {
        return match (true)
        {
            $pi->status !== PaymentStatus::SUCCEEDED->value => PaymentStatus::DEFAULT,
            $received < $expected => PaymentStatus::UNDERPAID,
            $received > $expected => PaymentStatus::OVERPAID,
            default => PaymentStatus::SUCCEEDED
        };

    }
}
