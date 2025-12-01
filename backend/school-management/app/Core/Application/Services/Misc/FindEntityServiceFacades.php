<?php

namespace App\Core\Application\Services\Misc;

use App\Core\Application\UseCases\Payments\FindConceptByIdUseCase;
use App\Core\Application\UseCases\Payments\FindPaymentByIdUseCase;
use App\Core\Application\UseCases\User\FindUserUseCase;
use App\Core\Domain\Entities\Payment;
use App\Core\Domain\Entities\PaymentConcept;
use App\Core\Domain\Entities\User;

class FindEntityServiceFacades
{
    public function __construct(
        private FindConceptByIdUseCase $concept,
        private FindPaymentByIdUseCase $payment,
        private FindUserUseCase $user,
    )
    {
    }

    public function findConcept(int $id): PaymentConcept
    {
        return $this->concept->execute($id);
    }
    public function findPayment(int $id): Payment
    {
        return $this->payment->execute($id);
    }
    public function findUser(bool $forceRefresh): User
    {
        return $this->user->execute($forceRefresh);
    }
}
