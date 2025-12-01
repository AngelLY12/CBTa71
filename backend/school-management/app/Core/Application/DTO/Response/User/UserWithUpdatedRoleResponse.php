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
 *         property="totalUpdated",
 *         type="integer",
 *         description="Número total de usuarios actualizados",
 *         example=3
 *     )
 * )
 */
class UserWithUpdatedRoleResponse
{
    public function __construct(
        public readonly ?array $fullNames,
        public readonly ?array $curps,
        public readonly ?array $updatedRoles,
        public readonly int $totalUpdated
    )
    {

    }
}
