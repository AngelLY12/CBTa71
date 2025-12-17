<?php

namespace App\Core\Domain\Enum\Payment;

/**
 * @OA\Schema(
 *     schema="PaymentStatus",
 *     type="string",
 *     description="Estatus válidos de un pago",
 *     enum={"succeeded", "requires_action", "paid", "unpaid", "pending", "overpaid", "underpaid"},
 *     example="paid"
 * )
 */
enum PaymentStatus: string
{
    case SUCCEEDED = 'succeeded';
    case REQUIRES_ACTION = 'requires_action';
    case PAID = 'paid';
    case UNPAID = 'unpaid';
    case DEFAULT = 'pending';
    case OVERPAID = 'overpaid';
    case UNDERPAID = 'underpaid';

    public static function terminalStatuses(): array
    {
        return [
            self::SUCCEEDED->value,
            self::OVERPAID->value,
            self::PAID->value,
            self::REQUIRES_ACTION->value,
        ];
    }

    public static function reconcilableStatuses(): array
    {
        return [
            self::DEFAULT->value,
            self::UNPAID->value,
            self::REQUIRES_ACTION->value,
            self::PAID->value,
        ];
    }

}
