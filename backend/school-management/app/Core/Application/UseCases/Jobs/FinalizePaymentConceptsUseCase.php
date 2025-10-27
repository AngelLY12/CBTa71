<?php

namespace App\Core\Application\UseCases\Jobs;

use App\Core\Domain\Repositories\Query\Payments\PaymentConceptQueryRepInterface;

class FinalizePaymentConceptsUseCase
{
    public function __construct(
        private PaymentConceptQueryRepInterface $finalize
    )
    {

    }

    public function execute(): void
    {
        $this->finalize->finalizePaymentConcepts();
    }
}
