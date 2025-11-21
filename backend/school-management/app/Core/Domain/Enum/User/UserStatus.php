<?php

namespace App\Core\Domain\Enum\User;

/**
 * @OA\Schema(
 *     schema="UserStatus",
 *     type="string",
 *     description="Estatus valido de un usuario",
 *     enum={"activo", "baja", "eliminado"}
 * )
 */
enum UserStatus: string
{
    case ACTIVO = 'activo';
    case BAJA = 'baja';
    case ELIMINADO = 'eliminado';
}

