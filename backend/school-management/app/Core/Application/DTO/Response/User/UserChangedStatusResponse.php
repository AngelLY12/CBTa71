<?php

namespace App\Core\Application\DTO\Response\User;

/**
 * @OA\Schema(
 *     schema="UserChangedStatusResponse",
 *     type="object",
 *     description="Respuesta después de cambiar el estado de uno o más usuarios",
 *     @OA\Property(
 *         property="updatedUsers",
 *         type="array",
 *         description="Usuarios que han cambiado su estado",
 *         @OA\Items(
 *             type="object",
 *             @OA\Property(property="name", type="string", example="Juan Pérez"),
 *             @OA\Property(property="curp", type="string", example="PEPJ800101HDFRRN09"),
 *             @OA\Property(property="status", type="string", example="activo")
 *         )
 *     ),
 *     @OA\Property(
 *         property="newStatus",
 *         type="string",
 *         description="Nuevo estado asignado a los usuarios",
 *         example="activo"
 *     ),
 *     @OA\Property(
 *         property="totalUpdated",
 *         type="integer",
 *         description="Número total de usuarios actualizados",
 *         example=5
 *     )
 * )
 */
class UserChangedStatusResponse
{
    public function __construct(
        public readonly ?array $updatedUsers,
        public readonly ?string $newStatus,
        public readonly int $totalUpdated
    )
    {

    }
}
