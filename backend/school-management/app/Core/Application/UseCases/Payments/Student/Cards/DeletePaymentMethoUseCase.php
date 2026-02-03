<?php

namespace App\Core\Application\UseCases\Payments\Student\Cards;

use App\Core\Domain\Repositories\Command\Payments\PaymentMethodRepInterface;
use App\Core\Domain\Repositories\Query\Payments\PaymentMethodQueryRepInterface;
use App\Core\Domain\Repositories\Stripe\StripeGatewayInterface;

class DeletePaymentMethoUseCase
{
    public function __construct(
        private PaymentMethodRepInterface $pmRepo,
        private PaymentMethodQueryRepInterface $pmqRepo,
        private StripeGatewayInterface $stripe,
    )
    {
    }

    public function execute(int $paymentMethodId): bool
    {
        $paymentMethod=$this->pmqRepo->findById($paymentMethodId);
        $this->stripe->deletePaymentMethod($paymentMethod->stripe_payment_method_id);
        $this->pmRepo->delete($paymentMethod->id);
        return true;
    }
}
