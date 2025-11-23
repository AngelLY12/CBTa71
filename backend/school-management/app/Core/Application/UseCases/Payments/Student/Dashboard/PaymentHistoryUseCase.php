<?php

namespace App\Core\Application\UseCases\Payments\Student\Dashboard;

use App\Core\Application\DTO\Response\General\PaginatedResponse;
use App\Core\Application\Mappers\GeneralMapper;
use App\Core\Domain\Repositories\Query\Payments\PaymentQueryRepInterface;

class PaymentHistoryUseCase
{

    public function __construct(
        private PaymentQueryRepInterface $pqRepo,
    ) {}
    public function execute(int $userId, int $perPage, int $page): PaginatedResponse {
        $historyArray=$this->pqRepo->getPaymentHistory($userId, $perPage, $page);
        return GeneralMapper::toPaginatedResponse($historyArray->items(), $historyArray);

    }
}
