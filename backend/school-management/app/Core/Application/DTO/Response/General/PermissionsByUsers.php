<?php

namespace App\Core\Application\DTO\Response\General;

/**
 * @OA\Schema(
 *     schema="PermissionsByUsers",
 *     type="object",
 *     @OA\Property(property="role", type="string", example="student"),
 *     @OA\Property(property="users", type="array", @OA\Items(type="object"),
 *         nullable=true,
 *         description="Lista de usuarios a los que aplican los permisos"),
 *     @OA\Property(property="permissions", type="array", @OA\Items(type="object"),
 *         nullable=true,
 *         description="Lista de permisos de los usuarios")
 * )
 */
class PermissionsByUsers
{
    public function __construct(
        public readonly ?string $role,
        public readonly array $users,
        public readonly array $permissions
    )
    {
    }
}
