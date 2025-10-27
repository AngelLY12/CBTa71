<?php
namespace App\Core\Application\UseCases\Payments\Staff\Concepts;

use App\Core\Domain\Entities\PaymentConcept;
use App\Core\Domain\Repositories\Command\Payments\PaymentConceptRepInterface;
use App\Core\Domain\Utils\Validators\PaymentConceptValidator;

class DisablePaymentConceptUseCase
{
    public function __construct(
        private PaymentConceptRepInterface $pcRepo
    )
    {}

    public function execute(PaymentConcept $concept):PaymentConcept
    {
        PaymentConceptValidator::ensureValidStatusTransition($concept, 'desactivado');
        return $this->pcRepo->disable($concept);
    }
}
