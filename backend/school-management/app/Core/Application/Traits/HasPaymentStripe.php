<?php

namespace App\Core\Application\Traits;

use App\Core\Domain\Entities\Payment;
use App\Core\Domain\Entities\PaymentMethod;
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
        $paymentMethodDetails = $this->formatPaymentMethodDetails($charge->payment_method_details);
        $fields=[
            'payment_method_id' => $savedPaymentMethod?->id,
            'stripe_payment_method_id' => $charge?->payment_method,
            'status' => $pi->status,
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
}
