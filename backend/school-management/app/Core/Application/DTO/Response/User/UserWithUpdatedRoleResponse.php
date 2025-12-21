<?php

namespace App\Core\Application\DTO\Response\User;


/**
 * @OA\Schema(
 *     schema="UserWithUpdatedRoleResponse",
 *     type="object",
 *     description="Respuesta con los roles actualizados de varios usuarios",
 *     @OA\Property(
 *         property="fullNames",
 *         type="array",
 *         description="Nombres completos de los usuarios afectados",
 *         @OA\Items(type="string"),
 *         example={"Juan Pérez", "Ana Gómez"}
 *     ),
 *     @OA\Property(
 *         property="curps",
 *         type="array",
 *         description="CURPs de los usuarios afectados",
 *         @OA\Items(type="string"),
 *         example={"PEPJ800101HDFRRN09", "GOMA900202MDFLRN05"}
 *     ),
 *     @OA\Property(
 *         property="updatedRoles",
 *         type="object",
 *         description="Roles agregados y removidos",
 *         @OA\Property(property="added", type="array", @OA\Items(type="string"), example={"student"}),
 *         @OA\Property(property="removed", type="array", @OA\Items(type="string"), example={"guest"})
 *     ),
 *     @OA\Property(
 *          property="metadata",
 *          type="object",
 *          description="Datos extra sobre la operación",
 *          @OA\Property(property="totalFound", type="integer",  description="Número total de usuarios encontrados", example=30),
 *          @OA\Property(property="totalUpdated", type="integer",  description="Número total de usuarios afectados", example=20),
 *          @OA\Property(property="failed", type="integer", description="Número total de usuarios no afectados por fallo", example=10),
 *          @OA\Property(
 *              property="operations",
 *              type="object",
 *              description="Operaciones realizadas",
 *              @OA\Property(property="roles_removed", type="array", @OA\Items(type="string"), example={"student"}),
 *              @OA\Property(property="roles_added", type="array", @OA\Items(type="string"), example={"guest"}),
 *              @OA\Property(property="chunks_processed", type="integer", example=5)
 *          ),
 *     ),
 * )
 */
class UserWithUpdatedRoleResponse
{
    public function __construct(
        public readonly ?array $fullNames,
        public readonly ?array $curps,
        public readonly ?array $updatedRoles,
        public readonly ?array $metadata
    )
    {

    }
}
