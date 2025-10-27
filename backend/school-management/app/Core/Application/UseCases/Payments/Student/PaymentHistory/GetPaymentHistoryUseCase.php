<?php

namespace App\Core\Application\UseCases\Payments\Student\PaymentHistory;

use App\Core\Application\Mappers\PaymentMapper;
use App\Core\Domain\Entities\User;
use App\Core\Domain\Repositories\Query\Payments\PaymentQueryRepInterface;

class GetPaymentHistoryUseCase
{
     public function __construct(
        private PaymentQueryRepInterface $pqRepo
    ) {}

    public function execute(User $user): array {
        $pamentsArray= $this->pqRepo->getPaymentHistoryWithDetails($user);
        return $pamentsArray;
    }

}
