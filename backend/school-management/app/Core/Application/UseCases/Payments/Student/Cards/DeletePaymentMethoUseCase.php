<?php

namespace App\Core\Application\UseCases\Payments\Student\Cards;

use App\Core\Domain\Repositories\Command\Payments\PaymentMethodRepInterface;
use App\Core\Domain\Repositories\Command\Stripe\StripeGatewayInterface;
use App\Core\Domain\Repositories\Query\Payments\PaymentMethodQueryRepInterface;

class DeletePaymentMethoUseCase
{
    public function __construct(
        private PaymentMethodRepInterface $pmRepo,
        private PaymentMethodQueryRepInterface $pmqRepo,
        private StripeGatewayInterface $stripe,
    )
    {
    }

    public function execute(string $paymentMethodId): bool
    {
        $paymentMethod=$this->pmqRepo->findById($paymentMethodId);
        $this->stripe->deletePaymentMethod($paymentMethod->stripe_payment_method_id);
        $this->pmRepo->delete($paymentMethod->id);
        return true;
    }
}
