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
 *     @OA\Property(property="applies_to", ref="#/components/schemas/PaymentConceptAppliesTo", example="todos"),
 *     @OA\Property(property="users", type="array", @OA\Items(type="string"), example={"12324","2435646","3323232"}),
 *     @OA\Property(property="careers", type="array", @OA\Items(type="string"), example={"Sistemas"}),
 *     @OA\Property(property="semesters", type="array", @OA\Items(type="integer"), example={1,2,3}),
 *     @OA\Property(property="exceptionUsers", type="array", @OA\Items(type="string"), example={"12326","24646","33232"}),
 *     @OA\Property(property="applicantTags", type="array", @OA\Items(ref="#/components/schemas/PaymentConceptApplicantType")),
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
        public readonly string $applies_to,
        public readonly array $users = [],
        public readonly array $careers = [],
        public readonly array $semesters = [],
        public readonly array $exceptionUsers = [],
        public readonly array $applicantTags =[],
        public readonly ?string $description=null,
        public readonly ?string $end_date=null,
    )
    {
    }

}
