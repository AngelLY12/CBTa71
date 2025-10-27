<?php

namespace App\Core\Application\UseCases\Payments\Student\Cards;

use App\Core\Application\DTO\Response\PaymentMethod\SetupCardResponse;
use App\Core\Application\Mappers\PaymentMethodMapper;
use App\Core\Domain\Entities\User;
use App\Core\Domain\Repositories\Command\Stripe\StripeGatewayInterface;

class SetupCardUseCase
{
 public function __construct(
        private StripeGatewayInterface $stripe
    )
    {
    }

    public function execute(User $user): SetupCardResponse
    {
        $session = $this->stripe->createSetupSession($user);
        return PaymentMethodMapper::toSetupCardResponse($session);
    }
}
