<?php

namespace App\Core\Application\DTO\Response\General;

/**
 * @OA\Schema(
 *     schema="PermissionToDisplay",
 *     title="Permission To Display",
 *     description="Permission data formatted for UI display purposes",
 *     @OA\Property(
 *          example=1,
 *          description="Unique permission ID"
 *      ),
 *      @OA\Property(
 *          example="users.create",
 *          description="Internal permission name"
 *      ),
 *      @OA\Property(
 *          example="model",
 *          description="Permission type"
 *      ),
 *      @OA\Property(
 *          example="Crear usuarios",
 *          description="Label para lectura"
 *      ),
 *      @OA\Property(
 *      example="Usuarios",
 *      description="UI grupo o categoria del permiso"
 *  )
 *
 * )
 *
 */
class PermissionToDisplay
{
    public function __construct(
        public readonly ?int $id,
        public readonly ?string $name,
        public readonly ?string $type,
        public readonly ?string $label,
        public readonly ?string $group
    ){}

}
