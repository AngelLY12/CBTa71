<?php

namespace App\Core\Domain\Utils\Validators;

use App\Core\Domain\Entities\Payment;
use App\Exceptions\NotAllowed\PaymentRetryNotAllowedException;

class PaymentValidator
{

    public static function ensurePaymentIsValidToRepay(Payment $payment)
    {
        $canRepay= $payment->isNonPaid() && $payment->isRecentPayment() && is_null($payment->amount_received);
        if(!$canRepay)
        {
            throw new PaymentRetryNotAllowedException();
        }
    }

}
