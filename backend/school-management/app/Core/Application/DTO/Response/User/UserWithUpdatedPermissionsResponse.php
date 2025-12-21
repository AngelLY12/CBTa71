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
 *           property="metadata",
 *           type="object",
 *           description="Datos extra sobre la operación",
 *           @OA\Property(property="totalFound", type="integer",  description="Número total de usuarios encontrados", example=30),
 *           @OA\Property(property="totalUpdated", type="integer",  description="Número total de usuarios afectados", example=20),
 *           @OA\Property(property="failed", type="integer", description="Número total de usuarios no afectados por fallo", example=10),
 *           @OA\Property(property="failedUsers", type="array", description="Lista de ID de usuarios fallidos", @OA\Items(type="string"),
 *              example={1,2,3,4}),
 *           @OA\Property(
 *              property="operations",
 *              type="object",
 *              description="Operaciones realizadas",
 *              @OA\Property(property="permissions_removed", type="array", @OA\Items(type="string"), example={"view"}),
 *              @OA\Property(property="permissions_added", type="array", @OA\Items(type="string"), example={"create"}),
 *              @OA\Property(property="roles_processed", type="integer", example=5)
 *          ),
 *     ),
 * )
 */
class UserWithUpdatedPermissionsResponse
{
    public function __construct(
        public readonly ?string $fullName,
        public readonly ?string $curp,
        public readonly ?string $role=null,
        public readonly array $updatedPermissions,
        public readonly ?array $metadata
    )
    {
    }
}
