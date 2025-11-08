<?php

namespace App\Core\Application\Services;

use App\Core\Application\UseCases\FindCareerByIdUseCase;
use App\Core\Application\UseCases\FindStudentDetailUseCase;
use App\Core\Application\UseCases\FindUserUseCase;
use App\Core\Application\UseCases\Payments\FindConceptByIdUseCase;
use App\Core\Application\UseCases\Payments\FindPaymentByIdUseCase;
use App\Core\Domain\Entities\Career;
use App\Core\Domain\Entities\Payment;
use App\Core\Domain\Entities\PaymentConcept;
use App\Core\Domain\Entities\StudentDetail;
use App\Core\Domain\Entities\User;

class FindEntityService
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
