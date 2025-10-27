<?php

namespace App\Core\Application\Services\Payments\Staff;

use App\Core\Application\DTO\Response\General\DashboardDataResponse;
use App\Core\Application\DTO\Response\PaymentConcept\PendingSummaryResponse;
use App\Core\Application\Mappers\GeneralMapper;
use App\Core\Application\UseCases\Payments\Staff\Dashboard\GetAllConceptsUseCase;
use App\Core\Application\UseCases\Payments\Staff\Dashboard\GetAllStudentsUseCase;
use App\Core\Application\UseCases\Payments\Staff\Dashboard\PaymentsMadeUseCase;
use App\Core\Application\UseCases\Payments\Staff\Dashboard\PendingPaymentAmountUseCase;

class DashboardServiceFacades{
    public function __construct(
        private PendingPaymentAmountUseCase $pending,
        private GetAllStudentsUseCase $students,
        private PaymentsMadeUseCase $payments,
        private GetAllConceptsUseCase $concepts
    )
    {
    }

    public function pendingPaymentAmount(bool $onlyThisYear = false): PendingSummaryResponse
    {
        return $this->pending->execute($onlyThisYear);
    }


    public function getAllStudents(bool $onlyThisYear = false): int
    {
        return $this->students->execute($onlyThisYear);
    }


    public function paymentsMade(bool $onlyThisYear = false):int
    {
        return $this->payments->execute($onlyThisYear);

    }

    public function getAllConcepts(bool $onlyThisYear = false):array
    {
        return $this->concepts->execute($onlyThisYear);

    }

    public function getData(bool $onlyThisYear = false):DashboardDataResponse
    {
        return GeneralMapper::toDashboardDataResponse(
            $this->paymentsMade($onlyThisYear),
            $this->pendingPaymentAmount($onlyThisYear),
            $this->getAllStudents($onlyThisYear));
    }
}
