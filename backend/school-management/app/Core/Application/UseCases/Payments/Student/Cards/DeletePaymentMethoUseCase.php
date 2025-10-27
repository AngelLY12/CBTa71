<?php

namespace App\Core\Application\UseCases\Payments\Student\Cards;

use App\Core\Domain\Repositories\Command\Payments\PaymentMethodRepInterface;
use App\Core\Domain\Repositories\Command\Stripe\StripeGatewayInterface;

class DeletePaymentMethoUseCase
{
    public function __construct(
        private PaymentMethodRepInterface $pmRepo,
        private StripeGatewayInterface $stripe,
    )
    {
    }

    public function execute(string $paymentMethodId): bool
    {
        $paymentMethod=$this->pmRepo->findById($paymentMethodId);
        $this->stripe->deletePaymentMethod($paymentMethod->stripe_payment_method_id);
        $this->pmRepo->delete($paymentMethod);
        return true;
    }
}
