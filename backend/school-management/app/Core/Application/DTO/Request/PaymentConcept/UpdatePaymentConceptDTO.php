<?php

namespace App\Core\Application\DTO\Request\PaymentConcept;

use App\Core\Domain\Enum\PaymentConcept\PaymentConceptAppliesTo;

/**
 * @OA\Schema(
 *     schema="UpdatePaymentConceptDTO",
 *     type="object",
 *     description="Datos para actualizar un concepto de pago",
 *     required={"id","fieldsToUpdate"},
 *     @OA\Property(property="id", type="integer", example=1, description="ID del concepto a actualizar"),
 *     @OA\Property(
 *         property="fieldsToUpdate",
 *         type="object",
 *         description="Campos del concepto a actualizar con sus nuevos valores",
 *         example={"concept_name":"Pago matrícula","amount":"2000.00","status":"activo"}
 *     ),
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
 *     @OA\Property(
 *         property="students",
 *         type="array",
 *         @OA\Items(type="string"),
 *         nullable=true,
 *         example={"12345","67891"},
 *         description="Numeros de control de estudiantes asociados al concepto"
 *     ),
 *     @OA\Property(property="appliesTo", ref="#/components/schemas/PaymentConceptAppliesTo", nullable=true, example="todos", description="A quién aplica el concepto"),
 *     @OA\Property(property="replaceRelations", type="boolean", example=false, description="Si es true, reemplaza las relaciones existentes con las nuevas")
 * )
 */
class UpdatePaymentConceptDTO
{
    public function __construct(
        public int $id,
        public array $fieldsToUpdate,
        public array|int|null $semesters = null,
        public array|int|null $careers = null,
        public array|string|null $students = null,
        public ?PaymentConceptAppliesTo $appliesTo = null,
        public bool $replaceRelations = false
    ) {}
}
