<?php

namespace App\Core\Application\UseCases\Payments\Staff\Concepts;

use App\Core\Domain\Entities\PaymentConcept;
use App\Core\Domain\Enum\PaymentConcept\PaymentConceptStatus;
use App\Core\Domain\Utils\Validators\PaymentConceptValidator;

class FinalizePaymentConceptUseCase extends BasePaymentConceptStatusUseCase
{
    protected function getTargetStatus(): PaymentConceptStatus
    {
        return PaymentConceptStatus::FINALIZADO;
    }

    protected function getRepositoryMethod(): string
    {
        return 'finalize';
    }
    public function execute(PaymentConcept $concept): PaymentConcept
    {
        PaymentConceptValidator::ensureConceptHasStarted($concept);
        return parent::execute($concept);
    }
}
