<?php

namespace App\Core\Application\UseCases\Payments\Student\PaymentHistory;

use App\Core\Application\DTO\Response\General\PaginatedResponse;
use App\Core\Application\Mappers\GeneralMapper;
use App\Core\Application\Mappers\PaymentMapper;
use App\Core\Domain\Entities\User;
use App\Core\Domain\Repositories\Query\Payments\PaymentQueryRepInterface;

class GetPaymentHistoryUseCase
{
     public function __construct(
        private PaymentQueryRepInterface $pqRepo
    ) {}

    public function execute(int $userId, int $perPage, int $page): PaginatedResponse {
        $paymentsPaginated= $this->pqRepo->getPaymentHistoryWithDetails($userId, $perPage, $page);
        return GeneralMapper::toPaginatedResponse($paymentsPaginated->items(), $paymentsPaginated);
    }

}
