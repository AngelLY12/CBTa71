<?php

namespace App\Core\Application\UseCases\Payments\Student\PaymentHistory;

use App\Core\Domain\Entities\Payment;
use App\Core\Domain\Repositories\Query\Payments\PaymentQueryRepInterface;
use App\Exceptions\NotFound\PaymentNotFountException;

class FindPaymentByIdUseCase
{
    public function __construct(
        private PaymentQueryRepInterface $paymentRepo
    )
    {
    }

    public function execute(int $id): Payment
    {
        $payment = $this->paymentRepo->findById($id);
        if(!$payment)
        {
            throw new PaymentNotFountException();
        }
        return $payment;
    }
}
