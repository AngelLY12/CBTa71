<?php

namespace App\Core\Application\UseCases\Payments\Student\Dashboard;

use App\Core\Application\DTO\Response\General\PaginatedResponse;
use App\Core\Application\Mappers\GeneralMapper;
use App\Core\Application\Mappers\PaymentMapper;
use App\Core\Domain\Entities\User;
use App\Core\Domain\Repositories\Query\Payments\PaymentQueryRepInterface;

class PaymentHistoryUseCase
{

    public function __construct(
        private PaymentQueryRepInterface $pqRepo,
    ) {}
    public function execute(User $user, int $perPage, int $page): PaginatedResponse {
        $historyArray=$this->pqRepo->getPaymentHistory($user, $perPage, $page);
        return GeneralMapper::toPaginatedResponse($historyArray->items(), $historyArray);

    }
}
