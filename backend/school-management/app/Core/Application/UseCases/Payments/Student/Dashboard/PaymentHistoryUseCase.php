<?php

namespace App\Core\Application\UseCases\Payments\Student\Dashboard;

use App\Core\Application\Mappers\PaymentMapper;
use App\Core\Domain\Entities\User;
use App\Core\Domain\Repositories\Query\Payments\PaymentQueryRepInterface;

class PaymentHistoryUseCase
{

    public function __construct(
        private PaymentQueryRepInterface $pqRepo,
    ) {}
    public function execute(User $user): array {
        $historyArray=$this->pqRepo->getPaymentHistory($user);
        return $historyArray;

    }
}
