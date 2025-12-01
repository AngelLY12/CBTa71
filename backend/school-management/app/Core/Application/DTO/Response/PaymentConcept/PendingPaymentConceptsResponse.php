<?php

namespace App\Core\Application\DTO\Response\PaymentConcept;

/**
 * @OA\Schema(
 *     schema="PendingPaymentConceptsResponse",
 *     type="object",
 *     @OA\Property(property="id", type="integer", nullable=true, description="ID del concepto pendiente", example=1),
 *     @OA\Property(property="concept_name", type="string", nullable=true, description="Nombre del concepto pendiente", example="Pago de inscripción"),
 *     @OA\Property(property="description", type="string", nullable=true, description="Descripción del concepto", example="Pago correspondiente al semestre 2025-2"),
 *     @OA\Property(property="amount", type="string", nullable=true, description="Monto del concepto pendiente", example="1500.00"),
 *     @OA\Property(property="start_date", type="string", format="date", nullable=true, description="Fecha de inicio del concepto pendiente", example="2025-11-01"),
 *     @OA\Property(property="end_date", type="string", format="date", nullable=true, description="Fecha de finalización del concepto pendiente", example="2025-12-01")
 * )
 */
class PendingPaymentConceptsResponse {
    public function __construct(
        public readonly ?int $id,
        public readonly ?string $concept_name,
        public readonly ?string $description,
        public readonly ?string $amount,
        public readonly ?string $start_date,
        public readonly ?string $end_date
    ) {}
}

