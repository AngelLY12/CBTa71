<?php

namespace App\Core\Application\DTO\Response\PaymentConcept;


/**
 * @OA\Schema(
 *     schema="UpdatePaymentConceptResponse",
 *     type="object",
 *     required={"id", "conceptName", "status", "appliesTo", "amount", "startDate", "endDate", "metadata", "message", "updatedAt"},
 *     @OA\Property(property="id", type="integer", example=123),
 *     @OA\Property(property="conceptName", type="string", example="Matrícula 2024"),
 *     @OA\Property(property="status", type="string", enum={"active", "inactive", "pending"}, example="active"),
 *     @OA\Property(property="appliesTo", type="string", enum={"career", "semester", "students", "career_semester", "tag", "all"}, example="career"),
 *     @OA\Property(property="description", type="string", nullable=true, example="Pago de matrícula del semestre"),
 *     @OA\Property(property="amount", type="number", format="float", example=1500.00),
 *     @OA\Property(property="startDate", type="string", format="date", example="2024-01-15"),
 *     @OA\Property(property="endDate", type="string", format="date", example="2024-02-15"),
 *     @OA\Property(
 *         property="metadata",
 *         type="object",
 *         @OA\Property(property="is_global", type="boolean", example=false),
 *         @OA\Property(property="exception_count", type="integer", example=5),
 *         @OA\Property(property="career_count", type="integer", example=3),
 *         @OA\Property(property="semester_count", type="integer", example=2)
 *     ),
 *     @OA\Property(property="message", type="string", example="Concepto actualizado: Ahora aplica a career. Se agregaron 2 careers"),
 *     @OA\Property(property="updatedAt", type="string", format="date-time", example="2024-01-12 14:45:00"),
 *     @OA\Property(
 *         property="changes",
 *         type="array",
 *         @OA\Items(
 *             type="object",
 *             @OA\Property(property="field", type="string", example="applies_to"),
 *             @OA\Property(property="old", type="string", example="students"),
 *             @OA\Property(property="new", type="string", example="career"),
 *             @OA\Property(property="type", type="string", example="applies_to_changed")
 *         )
 *     ),
 *     @OA\Property(
 *          property="affectedSummary",
 *          type="array",
 *          @OA\Items(
 *              type="object",
 *              @OA\Property(property="newlyAffectedCount", type="integer", example=50),
 *              @OA\Property(property="removedCount", type="integer", example=10),
 *              @OA\Property(property="keptCount", type="integer", example=0),
 *              @OA\Property(property="totalAffectedCount", type="integer", example=60),
 *              @OA\Property(property="previouslyAffectedCount", type="integer", example=20)
 *          )
 *      ),
 * )
 */
class UpdatePaymentConceptResponse
{
    public function __construct(
        public readonly int $id,
        public readonly string $conceptName,
        public readonly string $status,
        public readonly string $appliesTo,
        public readonly ?string $description,
        public readonly string $amount,
        public readonly string $startDate,
        public readonly string $endDate,
        public readonly array $metadata,
        public readonly string $message,
        public readonly string $updatedAt,
        public readonly array $changes = [],
        public readonly ?array $affectedSummary = []
    )
    {}

}
