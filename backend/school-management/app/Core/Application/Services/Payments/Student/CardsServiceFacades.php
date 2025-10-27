<?php

namespace App\Core\Application\Services\Payments\Student;

use App\Core\Application\DTO\Response\PaymentMethod\SetupCardResponse;
use App\Core\Application\UseCases\Payments\Student\Cards\DeletePaymentMethoUseCase;
use App\Core\Application\UseCases\Payments\Student\Cards\GetUserPaymentMethodsUseCase;
use App\Core\Application\UseCases\Payments\Student\Cards\SetupCardUseCase;
use App\Core\Domain\Entities\User;

class CardsServiceFacades
{


    public function __construct(
        private SetupCardUseCase $setup,
        private DeletePaymentMethoUseCase $delete,
        private GetUserPaymentMethodsUseCase $show
    )
    {
    }
    public function setupCard(User $user): SetupCardResponse
    {
        return $this->setup->execute($user);
    }
    public function deletePaymentMethod(string $paymentMethodId): bool
    {
       return $this->delete->execute($paymentMethodId);
    }

    public function getUserPaymentMethods(User $user): array
    {
        return $this->show->execute($user);
    }

}
