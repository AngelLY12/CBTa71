<?php

namespace App\Core\Application\Services\Payments\Staff;

use App\Core\Application\DTO\Request\PaymentConcept\CreatePaymentConceptDTO;
use App\Core\Application\DTO\Request\PaymentConcept\UpdatePaymentConceptDTO;
use App\Core\Domain\Entities\PaymentConcept;
use App\Core\Application\UseCases\Payments\Staff\Concepts\ActivatePaymentConceptUseCase;
use App\Core\Application\UseCases\Payments\Staff\Concepts\CreatePaymentConceptUseCase;
use App\Core\Application\UseCases\Payments\Staff\Concepts\DisablePaymentConceptUseCase;
use App\Core\Application\UseCases\Payments\Staff\Concepts\EliminateLogicalPaymentConceptUseCase;
use App\Core\Application\UseCases\Payments\Staff\Concepts\EliminatePaymentConceptUseCase;
use App\Core\Application\UseCases\Payments\Staff\Concepts\FinalizePaymentConceptUseCase;
use App\Core\Application\UseCases\Payments\Staff\Concepts\ShowConceptsUseCase;
use App\Core\Application\UseCases\Payments\Staff\Concepts\UpdatePaymentConceptUseCase;

class ConceptsServiceFacades{

    public function __construct(
        private ShowConceptsUseCase $show,
        private CreatePaymentConceptUseCase $create,
        private UpdatePaymentConceptUseCase $update,
        private FinalizePaymentConceptUseCase $finalize,
        private DisablePaymentConceptUseCase $disable,
        private EliminatePaymentConceptUseCase $eliminate,
        private EliminateLogicalPaymentConceptUseCase $eliminateLogical,
        private ActivatePaymentConceptUseCase $activate
    )
    {}

    public function showConcepts(string $status = 'todos'): array {
        return $this->show->execute($status);
    }

     public function createPaymentConcept(CreatePaymentConceptDTO $dto): PaymentConcept {
        return $this->create->execute($dto);
    }

    public function updatePaymentConcept(UpdatePaymentConceptDTO $dto): PaymentConcept {
        return $this->update->execute($dto);
    }

    public function finalizePaymentConcept(PaymentConcept $concept): PaymentConcept {
        return $this->finalize->execute($concept);
    }

    public function disablePaymentConcept(PaymentConcept $concept): PaymentConcept {
        return $this->disable->execute($concept);
    }

    public function eliminatePaymentConcept(PaymentConcept $concept): void {
        $this->eliminate->execute($concept);
    }

    public function activatePaymentConcept(PaymentConcept $concept):PaymentConcept
    {
        return $this->activate->execute($concept);
    }

    public function elminateLogicalPaymentConcept(PaymentConcept $concept): PaymentConcept{
        return $this->eliminateLogical->execute($concept);
    }
}
