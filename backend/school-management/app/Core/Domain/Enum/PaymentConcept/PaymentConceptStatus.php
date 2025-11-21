<?php

namespace App\Core\Domain\Enum\PaymentConcept;

/**
 * @OA\Schema(
 *     schema="PaymentConceptStatus",
 *     type="string",
 *     description="Estatus válidos de un concepto de pago",
 *     enum={"activo", "finalizado", "desactivado", "eliminado"}
 * )
 */
enum PaymentConceptStatus: string
{
    case ACTIVO = 'activo';
    case FINALIZADO = 'finalizado';
    case DESACTIVADO = 'desactivado';
    case ELIMINADO = 'eliminado';

     public function canTransitionTo(self $new): bool
    {
        return match ($this) {
            self::ACTIVO       => in_array($new, [self::FINALIZADO, self::ELIMINADO, self::DESACTIVADO], true),
            self::FINALIZADO   => in_array($new, [self::ACTIVO, self::ELIMINADO], true),
            self::ELIMINADO    => $new === self::ACTIVO,
            self::DESACTIVADO  => in_array($new, [self::ACTIVO, self::ELIMINADO], true),
        };
    }

    public function isUpdatable(): bool
    {
        return in_array($this, [self::ACTIVO, self::DESACTIVADO], true);
    }
}




