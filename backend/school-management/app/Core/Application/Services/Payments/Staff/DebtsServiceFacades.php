<?php

namespace App\Core\Application\Services\Payments\Staff;

use App\Core\Application\DTO\Response\General\PaginatedResponse;
use App\Core\Application\DTO\Response\Payment\PaymentValidateResponse;
use App\Core\Application\UseCases\Payments\Staff\Debts\GetPaymentsFromStripeUseCase;
use App\Core\Application\UseCases\Payments\Staff\Debts\ShowAllPendingPaymentsUseCase;
use App\Core\Application\UseCases\Payments\Staff\Debts\ValidatePaymentUseCase;

class DebtsServiceFacades{
    public function __construct(
        private ShowAllPendingPaymentsUseCase $pending,
        private ValidatePaymentUseCase $validate,
        private GetPaymentsFromStripeUseCase $payments
    )
    {}
    public function showAllpendingPayments(?string $search=null, int $perPage = 15): PaginatedResponse
    {
        return $this->pending->execute($search, $perPage);
    }

    public function validatePayment(string $search, string $payment_intent_id): PaymentValidateResponse
    {
        return $this->validate->execute($search,$payment_intent_id);
    }

    public function getPaymentsFromStripe(string $search, ?int $year=null):array
    {
        return $this->payments->execute($search,$year);
    }
}
