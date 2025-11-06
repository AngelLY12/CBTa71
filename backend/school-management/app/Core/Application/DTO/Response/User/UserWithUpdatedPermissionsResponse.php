<?php

namespace App\Core\Application\DTO\Response\User;

/**
 * @OA\Schema(
 *     schema="UserWithUpdatedPermissionsResponse",
 *     type="object",
 *     @OA\Property(property="fullName", type="string", description="Nombre completo del usuario", example="Juan Pérez"),
 *     @OA\Property(property="curp", type="string", description="CURP del usuario", example="PEPJ800101HDFRRN09"),
 *     @OA\Property(property="updatedPermissions", type="array", description="Lista de permisos actualizados para el usuario", @OA\Items(type="string"), example={"find user","create payment"})
 * )
 */
class UserWithUpdatedPermissionsResponse
{
    public function __construct(
        public readonly string $fullName,
        public readonly string $curp,
        public readonly array $updatedPermissions
    )
    {

    }
}
