<?php

namespace App\Core\Application\Services\Payments\Staff;

use App\Core\Application\DTO\Request\PaymentConcept\CreatePaymentConceptDTO;
use App\Core\Application\DTO\Request\PaymentConcept\UpdatePaymentConceptDTO;
use App\Core\Application\DTO\Request\PaymentConcept\UpdatePaymentConceptRelationsDTO;
use App\Core\Application\DTO\Response\General\PaginatedResponse;
use App\Core\Application\DTO\Response\PaymentConcept\ConceptChangeStatusResponse;
use App\Core\Application\DTO\Response\PaymentConcept\CreatePaymentConceptResponse;
use App\Core\Application\DTO\Response\PaymentConcept\UpdatePaymentConceptRelationsResponse;
use App\Core\Application\DTO\Response\PaymentConcept\UpdatePaymentConceptResponse;
use App\Core\Application\Traits\HasCache;
use App\Core\Application\UseCases\Payments\Staff\Concepts\UpdatePaymentConceptRelationsUseCase;
use App\Core\Domain\Entities\PaymentConcept;
use App\Core\Application\UseCases\Payments\Staff\Concepts\ActivatePaymentConceptUseCase;
use App\Core\Application\UseCases\Payments\Staff\Concepts\CreatePaymentConceptUseCase;
use App\Core\Application\UseCases\Payments\Staff\Concepts\DisablePaymentConceptUseCase;
use App\Core\Application\UseCases\Payments\Staff\Concepts\EliminateLogicalPaymentConceptUseCase;
use App\Core\Application\UseCases\Payments\Staff\Concepts\EliminatePaymentConceptUseCase;
use App\Core\Application\UseCases\Payments\Staff\Concepts\FinalizePaymentConceptUseCase;
use App\Core\Application\UseCases\Payments\Staff\Concepts\ShowConceptsUseCase;
use App\Core\Application\UseCases\Payments\Staff\Concepts\UpdatePaymentConceptFieldsUseCase;
use App\Core\Domain\Enum\Cache\CachePrefix;
use App\Core\Domain\Enum\Cache\StaffCacheSufix;
use App\Core\Infraestructure\Cache\CacheService;

class ConceptsServiceFacades{
    use HasCache;

    public function __construct(
        private ShowConceptsUseCase                   $show,
        private CreatePaymentConceptUseCase           $create,
        private UpdatePaymentConceptFieldsUseCase     $update,
        private UpdatePaymentConceptRelationsUseCase $updateRelations,
        private FinalizePaymentConceptUseCase         $finalize,
        private DisablePaymentConceptUseCase          $disable,
        private EliminatePaymentConceptUseCase        $eliminate,
        private EliminateLogicalPaymentConceptUseCase $eliminateLogical,
        private ActivatePaymentConceptUseCase         $activate,
        private CacheService                          $service
    )
    {
        $this->setCacheService($service);

    }

    public function showConcepts(string $status, int $perPage, int $page, bool $forceRefresh): PaginatedResponse{
        $key = $this->service->makeKey(CachePrefix::STAFF->value, StaffCacheSufix::CONCEPTS->value . ":list:$status:$perPage:$page");
        return $this->cache($key,$forceRefresh ,fn() => $this->show->execute($status, $perPage, $page));
    }

     public function createPaymentConcept(CreatePaymentConceptDTO $dto): CreatePaymentConceptResponse {
        $concept = $this->create->execute($dto);
        $this->service->clearKey(CachePrefix::STAFF->value, StaffCacheSufix::CONCEPTS->value . ":list:{$concept->status}");
        return $concept;
    }

    public function updatePaymentConcept(UpdatePaymentConceptDTO $dto): UpdatePaymentConceptResponse {
        $concept = $this->update->execute($dto);
        $this->service->clearKey(CachePrefix::STAFF->value, StaffCacheSufix::CONCEPTS->value . ":list:{$concept->status}");
        return $concept;
    }

    public function updatePaymentConceptRelations(UpdatePaymentConceptRelationsDTO $dto): UpdatePaymentConceptRelationsResponse
    {
        $concept= $this->updateRelations->execute($dto);
        $this->service->clearKey(CachePrefix::STAFF->value, StaffCacheSufix::CONCEPTS->value . ":list:{$concept->status}");
        return $concept;
    }

    public function finalizePaymentConcept(PaymentConcept $concept): ConceptChangeStatusResponse {
        $oldStatus = $concept->status;
        $result = $this->finalize->execute($concept);
        $this->service->clearKey(CachePrefix::STAFF->value, StaffCacheSufix::CONCEPTS->value . ":list:{$oldStatus->value}");
        $this->service->clearKey(CachePrefix::STAFF->value, StaffCacheSufix::CONCEPTS->value . ":list:{$result->status->value}");
        return $result;
    }

    public function disablePaymentConcept(PaymentConcept $concept): ConceptChangeStatusResponse {
        $oldStatus = $concept->status;
        $result = $this->disable->execute($concept);
        $this->service->clearKey(CachePrefix::STAFF->value, StaffCacheSufix::CONCEPTS->value . ":list:{$oldStatus->value}");
        $this->service->clearKey(CachePrefix::STAFF->value, StaffCacheSufix::CONCEPTS->value . ":list:{$result->status->value}");
        return $result;
    }

    public function eliminatePaymentConcept(int $conceptId): void {
        $this->eliminate->execute($conceptId);
        $this->service->clearKey(CachePrefix::STAFF->value, StaffCacheSufix::CONCEPTS->value . ":list");
    }

    public function activatePaymentConcept(PaymentConcept $concept):ConceptChangeStatusResponse
    {
        $oldStatus = $concept->status;
        $result = $this->activate->execute($concept);
        $this->service->clearKey(CachePrefix::STAFF->value, StaffCacheSufix::CONCEPTS->value . ":list:{$oldStatus->value}");
        $this->service->clearKey(CachePrefix::STAFF->value, StaffCacheSufix::CONCEPTS->value . ":list:{$result->status->value}");
        return $result;
    }

    public function eliminateLogicalPaymentConcept(PaymentConcept $concept): ConceptChangeStatusResponse{
        $result = $this->eliminateLogical->execute($concept);
        $this->service->clearKey(CachePrefix::STAFF->value, StaffCacheSufix::CONCEPTS->value . ":list");
        return $result;
    }
}
