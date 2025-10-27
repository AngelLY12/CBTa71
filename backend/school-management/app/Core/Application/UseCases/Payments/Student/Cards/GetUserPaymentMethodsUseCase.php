<?php

namespace App\Core\Application\UseCases\Payments\Student\Cards;

use App\Core\Application\Mappers\PaymentMethodMapper;
use App\Core\Domain\Entities\User;
use App\Core\Domain\Repositories\Command\Payments\PaymentMethodRepInterface;

class GetUserPaymentMethodsUseCase
{
    public function __construct(
        private PaymentMethodRepInterface $pmRepo,
    )
    {
    }
    public function execute(User $user): array
    {
         $methods = $this->pmRepo->getByUserId($user);

        return array_map(
            fn($method) => PaymentMethodMapper::toDisplayPaymentMethodResponse($method),
            $methods
        );
    }

}
