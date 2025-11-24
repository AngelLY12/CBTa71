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

    public function allowedTransitions(): array
    {
        return match ($this) {
            self::ACTIVO => [self::FINALIZADO, self::DESACTIVADO, self::ELIMINADO,],
            self::FINALIZADO => [self::ACTIVO, self::ELIMINADO,],
            self::DESACTIVADO => [self::ACTIVO, self::ELIMINADO,],
            self::ELIMINADO => [self::ACTIVO,],
        };
    }

    public function canTransitionTo(self $newStatus): bool
    {
        return in_array($newStatus, $this->allowedTransitions(), true);
    }


    public function isUpdatable(): bool
    {
        return in_array($this, [self::ACTIVO, self::DESACTIVADO], true);
    }
}




