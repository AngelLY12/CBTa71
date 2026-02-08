<?php

namespace App\Swagger\Responses;

/**
 * @OA\Schema(
 *     schema="ConceptsListItem",
 *     type="object",
 *     description="Item de concepto para listado",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="concept_name", type="string", example="Pago de inscripción"),
 *     @OA\Property(property="amount", type="string", example="1,500.00"),
 *     @OA\Property(property="status", type="string", example="active"),
 *     @OA\Property(
 *         property="expiration_human",
 *         type="string",
 *         nullable=true,
 *         description="Texto humano que indica estado de expiración",
 *         example="Vence en 3 días"
 *     ),
 *     @OA\Property(
 *         property="has_expiration",
 *         type="boolean",
 *         description="Indica si el concepto tiene fecha de expiración",
 *         example=true
 *     )
 * )
 */
class ConceptsListItem
{

}
