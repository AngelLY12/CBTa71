<?php

namespace App\Core\Application\DTO\Response\User;


/**
 * @OA\Schema(
 *     schema="UserWithUpdatedRoleResponse",
 *     type="object",
 *     description="Respuesta con los roles actualizados de varios usuarios",
 *     @OA\Property(
 *         property="summary",
 *         type="object",
 *         description="Resumen global de la operación",
 *         @OA\Property(property="totalFound", type="integer", example=30, description="Total de usuarios encontrados"),
 *         @OA\Property(property="totalUpdated", type="integer", example=20, description="Total de usuarios actualizados"),
 *         @OA\Property(property="totalUnchanged", type="integer", example=5, description="Total de usuarios sin cambios"),
 *         @OA\Property(property="totalFailed", type="integer", example=5, description="Total de usuarios que fallaron"),
 *         @OA\Property(
 *             property="operations",
 *             type="object",
 *             description="Operaciones realizadas",
 *             @OA\Property(property="roles_removed", type="array", @OA\Items(type="string"), example={"student"}),
 *             @OA\Property(property="roles_added", type="array", @OA\Items(type="string"), example={"guest"}),
 *             @OA\Property(property="chunks_processed", type="integer", example=5)
 *         )
 *     ),
 *     @OA\Property(
 *         property="usersProcessed",
 *         type="object",
 *         description="Detalle de usuarios procesados",
 *         @OA\Property(
 *             property="processed_users",
 *             type="array",
 *             @OA\Items(type="integer"),
 *             example={1, 2, 3},
 *             description="IDs de primeros 10 usuarios procesados exitosamente"
 *         ),
 *         @OA\Property(
 *             property="unchanged_users",
 *             type="array",
 *             @OA\Items(type="integer"),
 *             example={4, 5},
 *             description="IDs de usuarios que no tuvieron cambios"
 *         ),
 *         @OA\Property(
 *             property="failed_users",
 *             type="array",
 *             @OA\Items(type="integer"),
 *             example={6, 7},
 *             description="IDs de usuarios que fallaron"
 *         )
 *     ),
 *     @OA\Property(
 *         property="updatedRoles",
 *         type="object",
 *         description="Roles agregados y removidos",
 *         @OA\Property(
 *             property="added",
 *             type="array",
 *             @OA\Items(type="string"),
 *             example={"student"}
 *         ),
 *         @OA\Property(
 *             property="removed",
 *             type="array",
 *             @OA\Items(type="string"),
 *             example={"guest"}
 *         )
 *     )
 * )
 */
class UserWithUpdatedRoleResponse
{
    public function __construct(
        public readonly ?array $summary,
        public readonly ?array $usersProcessed,
        public readonly ?array $updatedRoles,
    )
    {

    }
}
