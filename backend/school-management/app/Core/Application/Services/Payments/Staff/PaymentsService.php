<?php

namespace App\Core\Application\Services\Payments\Staff;

use App\Core\Application\DTO\Response\General\PaginatedResponse;
use App\Core\Application\Mappers\GeneralMapper;
use App\Core\Application\Mappers\PaymentMapper;
use App\Core\Application\UseCases\Payments\Staff\Payments\ShowAllPaymentUseCase;
use App\Core\Domain\Repositories\Query\Payments\PaymentQueryRepInterface;

class PaymentsService{

    public function __construct(
        private ShowAllPaymentUseCase $payments
    )
    {
    }
    public function showAllPayments(?string $search = null, int $perPage = 15): PaginatedResponse
    {
        return $this->payments->execute($search,$perPage);
    }
}
