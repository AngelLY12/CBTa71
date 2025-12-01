<?php

namespace App\Core\Application\DTO\Response\User;

/**
 * @OA\Schema(
 *     schema="UserWithUpdatedPermissionsResponse",
 *     type="object",
 *     description="Respuesta con los permisos actualizados de un usuario o grupo de usuarios",
 *     @OA\Property(property="fullName", type="string", description="Nombre completo del usuario", example="Juan Pérez"),
 *     @OA\Property(property="curp", type="string", description="CURP del usuario", example="PEPJ800101HDFRRN09"),
 *     @OA\Property(property="role", type="string", description="Rol actual del usuario", example="student"),
 *     @OA\Property(
 *         property="updatedPermissions",
 *         type="array",
 *         description="Lista de permisos actualizados (añadidos o removidos)",
 *         @OA\Items(type="string"),
 *         example={"find user", "create payment"}
 *     ),
 *     @OA\Property(
 *         property="totalUpdated",
 *         type="integer",
 *         description="Número total de usuarios afectados por la actualización",
 *         example=10
 *     )
 * )
 */
class UserWithUpdatedPermissionsResponse
{
    public function __construct(
        public readonly ?string $fullName,
        public readonly ?string $curp,
        public readonly ?string $role=null,
        public readonly array $updatedPermissions,
        public readonly int $totalUpdated
    )
    {
    }
}
