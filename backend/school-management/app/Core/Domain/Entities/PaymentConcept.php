<?php

namespace App\Core\Domain\Entities;

use App\Core\Domain\Enum\PaymentConcept\PaymentConceptAppliesTo;
use App\Core\Domain\Enum\PaymentConcept\PaymentConceptStatus;
use Carbon\Carbon;

/**
 * @OA\Schema(
 *     schema="DomainPaymentConcept",
 *     type="object",
 *     description="Representa un concepto de pago",
 *     @OA\Property(property="id", type="integer", nullable=true, example=1),
 *     @OA\Property(property="concept_name", type="string", example="Pago de inscripción"),
 *     @OA\Property(property="description", type="string", nullable=true, example="Pago correspondiente al semestre 2025A"),
 *     @OA\Property(property="status", ref="#/components/schemas/PaymentConceptStatus", example="activo"),
 *     @OA\Property(property="start_date", type="string", format="date", example="2025-09-01"),
 *     @OA\Property(property="end_date", type="string", format="date", nullable=true, example="2025-12-31"),
 *     @OA\Property(property="amount", type="string", example="1500.00"),
 *     @OA\Property(property="applies_to", ref="#/components/schemas/PaymentConceptAppliesTo", example="todos"),
 *     @OA\Property(property="is_global", type="boolean", example=true),
 *     @OA\Property(property="userIds", type="array", @OA\Items(type="integer"), example={1,2,3}),
 *     @OA\Property(property="careerIds", type="array", @OA\Items(type="integer"), example={1,2}),
 *     @OA\Property(property="semesters", type="array", @OA\Items(type="integer"), example={1,2,3})
 * )
 */
class PaymentConcept
{
    public function __construct(
        public ?int $id=null,
        public string $concept_name,
        public ?string $description=null,
        public PaymentConceptStatus $status,
        public Carbon $start_date,
        public ?Carbon $end_date=null,
        public string $amount,
        public PaymentConceptAppliesTo $applies_to,
        public bool $is_global,
        private array $userIds = [],
        private array $careerIds = [],
        private array $semesters = []
    ) {}

    public function isActive(): bool
    {
        return $this->status === PaymentConceptStatus::ACTIVO;
    }
    public function isDisable(): bool
    {
        return $this->status === PaymentConceptStatus::DESACTIVADO;
    }

    public function isFinalize(): bool
    {
        return $this->status === PaymentConceptStatus::FINALIZADO;
    }

    public function isDelete(): bool
    {
        return $this->status === PaymentConceptStatus::ELIMINADO;
    }

     public function isExpired(): bool
    {
        $today = Carbon::today();
        if ($this->end_date && $today > $this->end_date) {
            return true;
        }
        return false;
    }

    public function hasStarted(): bool
    {
        $today = Carbon::today();
        return $today >= $this->start_date;
    }

    public function setUserIds(array $ids): void { $this->userIds = $ids; }
    public function getUserIds(): array { return $this->userIds; }

    public function setCareerIds(array $ids): void { $this->careerIds = $ids; }
    public function getCareerIds(): array { return $this->careerIds; }

    public function setSemesters(array $semesters): void { $this->semesters = $semesters; }
    public function getSemesters(): array { return $this->semesters; }

}
