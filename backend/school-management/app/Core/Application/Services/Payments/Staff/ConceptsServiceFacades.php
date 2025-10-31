<?php

namespace App\Core\Application\Services\Payments\Staff;

use App\Core\Application\DTO\Request\PaymentConcept\CreatePaymentConceptDTO;
use App\Core\Application\DTO\Request\PaymentConcept\UpdatePaymentConceptDTO;
use App\Core\Application\DTO\Response\General\PaginatedResponse;
use App\Core\Application\Traits\HasCache;
use App\Core\Domain\Entities\PaymentConcept;
use App\Core\Application\UseCases\Payments\Staff\Concepts\ActivatePaymentConceptUseCase;
use App\Core\Application\UseCases\Payments\Staff\Concepts\CreatePaymentConceptUseCase;
use App\Core\Application\UseCases\Payments\Staff\Concepts\DisablePaymentConceptUseCase;
use App\Core\Application\UseCases\Payments\Staff\Concepts\EliminateLogicalPaymentConceptUseCase;
use App\Core\Application\UseCases\Payments\Staff\Concepts\EliminatePaymentConceptUseCase;
use App\Core\Application\UseCases\Payments\Staff\Concepts\FinalizePaymentConceptUseCase;
use App\Core\Application\UseCases\Payments\Staff\Concepts\ShowConceptsUseCase;
use App\Core\Application\UseCases\Payments\Staff\Concepts\UpdatePaymentConceptUseCase;
use App\Core\Infraestructure\Cache\CacheService;

class ConceptsServiceFacades{
    use HasCache;
    private string $prefix = 'concepts:list';

    public function __construct(
        private ShowConceptsUseCase $show,
        private CreatePaymentConceptUseCase $create,
        private UpdatePaymentConceptUseCase $update,
        private FinalizePaymentConceptUseCase $finalize,
        private DisablePaymentConceptUseCase $disable,
        private EliminatePaymentConceptUseCase $eliminate,
        private EliminateLogicalPaymentConceptUseCase $eliminateLogical,
        private ActivatePaymentConceptUseCase $activate,
        private CacheService $service
    )
    {}

    public function showConcepts(string $status, int $perPage, int $page, bool $forceRefresh): PaginatedResponse{
        $key = "$this->prefix:$status:$perPage:$page";
        return $this->cache($key,$forceRefresh ,fn() => $this->show->execute($status, $perPage, $page));
    }

     public function createPaymentConcept(CreatePaymentConceptDTO $dto): PaymentConcept {
        $concept = $this->create->execute($dto);
        $this->service->clearPrefix($this->prefix);
        return $concept;
    }

    public function updatePaymentConcept(UpdatePaymentConceptDTO $dto): PaymentConcept {
        $concept = $this->update->execute($dto);
        $this->service->clearPrefix($this->prefix);
        return $concept;
    }

    public function finalizePaymentConcept(PaymentConcept $concept): PaymentConcept {
        $result = $this->finalize->execute($concept);
        $this->service->clearPrefix($this->prefix);
        return $result;
    }

    public function disablePaymentConcept(PaymentConcept $concept): PaymentConcept {
        $result = $this->disable->execute($concept);
        $this->service->clearPrefix($this->prefix);
        return $result;
    }

    public function eliminatePaymentConcept(PaymentConcept $concept): void {
        $this->eliminate->execute($concept);
        $this->service->clearPrefix($this->prefix);
    }

    public function activatePaymentConcept(PaymentConcept $concept):PaymentConcept
    {
        $result = $this->activate->execute($concept);
        $this->service->clearPrefix($this->prefix);
        return $result;
    }

    public function eliminateLogicalPaymentConcept(PaymentConcept $concept): PaymentConcept{
        $result = $this->eliminateLogical->execute($concept);
        $this->service->clearPrefix($this->prefix);
        return $result;
    }
}
