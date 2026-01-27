<?php

namespace App\Core\Application\DTO\Response\PaymentConcept;

use App\Models\PaymentConcept;

/**
 * @OA\Schema(
 *     schema="ConceptToDisplay",
 *     type="object",
 *     description="Representa un concepto de pago",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="concept_name", type="string", example="Pago de inscripción"),
 *     @OA\Property(property="status", ref="#/components/schemas/PaymentConceptStatus", example="activo"),
 *     @OA\Property(property="start_date", type="string", format="date", example="2025-09-01"),
 *     @OA\Property(property="amount", type="string", example="1500.00"),
 *     @OA\Property(property="description", type="string", nullable=true, example="Pago correspondiente al semestre 2025A"),
 *     @OA\Property(property="end_date", type="string", format="date", nullable=true, example="2025-12-31"),
 * )
 */
class ConceptToDisplay
{
    public function __construct(
        public readonly int $id,
        public readonly string $concept_name,
        public readonly string $status,
        public readonly string $start_date,
        public readonly string $amount,
        public readonly ?string $description=null,
        public readonly ?string $end_date=null,
    )
    {
    }


}
