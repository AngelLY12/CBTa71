<?php
namespace App\Core\Application\UseCases\Payments\Staff\Concepts;

use App\Core\Domain\Entities\PaymentConcept;
use App\Core\Domain\Repositories\Command\Payments\PaymentConceptRepInterface;

class EliminatePaymentConceptUseCase
{
    public function __construct(
        private PaymentConceptRepInterface $pcRepo
    )
    {}

    public function execute(PaymentConcept $concept):void
    {
        $this->pcRepo->delete($concept);
    }
}
