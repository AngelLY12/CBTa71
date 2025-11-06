<?php

namespace App\Core\Application\DTO\Request\PaymentConcept;

use Carbon\Carbon;


/**
 * @OA\Schema(
 *     schema="CreatePaymentConceptDTO",
 *     type="object",
 *     description="Datos para crear un concepto de pago",
 *     required={"concept_name","amount","status","is_global"},
 *     @OA\Property(property="concept_name", type="string", example="Pago de inscripción"),
 *     @OA\Property(property="description", type="string", nullable=true, example="Pago correspondiente al semestre 2025A"),
 *     @OA\Property(property="amount", type="string", example="1500.00"),
 *     @OA\Property(property="status", type="string", example="activo"),
 *     @OA\Property(property="start_date", type="string", format="date", nullable=true, example="2025-09-01"),
 *     @OA\Property(property="end_date", type="string", format="date", nullable=true, example="2025-12-31"),
 *     @OA\Property(property="is_global", type="boolean", example=true),
 *     @OA\Property(property="appliesTo", type="string", example="todos"),
 *     @OA\Property(
 *         property="semesters",
 *         type="array",
 *         @OA\Items(type="integer"),
 *         nullable=true,
 *         example={1,2,3},
 *         description="Semestres asociados al concepto"
 *     ),
 *     @OA\Property(
 *         property="careers",
 *         type="array",
 *         @OA\Items(type="integer"),
 *         nullable=true,
 *         example={1,2},
 *         description="Carreras asociadas al concepto"
 *     ),
 *      @OA\Property(
 *         property="students",
 *         type="array",
 *         @OA\Items(type="string"),
 *         nullable=true,
 *         example={"12345","67891"},
 *         description="Numeros de control de estudiantes asociados al concepto"
 *      )
 * )
 */

class CreatePaymentConceptDTO {
    public function __construct(
        public string $concept_name,
        public ?string $description,
        public string $amount,
        public string $status,
        public ?Carbon $start_date,
        public ?Carbon $end_date,
        public bool $is_global,
        public string $appliesTo = 'todos',
        public array|int|null $semesters = null,
        public array|int|null $careers = null,
        public array|string|null $students = null,
    ) {}
}
