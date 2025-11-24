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

    public function allowedTransitions(): array
    {
        return match ($this) {
            self::ACTIVO => [self::BAJA, self::ELIMINADO],
            self::BAJA => [self::ACTIVO, self::ELIMINADO],
            self::ELIMINADO => [self::ACTIVO],
        };
    }

    public function canTransitionTo(self $newStatus): bool
    {
        return in_array($newStatus, $this->allowedTransitions(), true);
    }
     public function isUpdatable(): bool
    {
        return in_array($this, [self::ACTIVO], true);
    }
}

