<?php

namespace App\Core\Application\UseCases\Jobs;

use App\Core\Domain\Repositories\Command\Payments\PaymentConceptRepInterface;

class FinalizePaymentConceptsUseCase
{
    public function __construct(
        private PaymentConceptRepInterface $finalize
    )
    {

    }

    public function execute(): int
    {
        return $this->finalize->finalizePaymentConcepts();
    }
}
