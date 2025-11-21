<?php

namespace App\Core\Domain\Enum\Payment;

/**
 * @OA\Schema(
 *     schema="PaymentStatus",
 *     type="string",
 *     description="Estatus válidos de un pago",
 *     enum={"succeeded", "requires_action", "paid", "unpaid", "pending"}
 * )
 */
enum PaymentStatus: string
{
    case SUCCEEDED = 'succeeded';
    case REQUIRES_ACTION = 'requires_action';
    case PAID = 'paid';
    case UNPAID = 'unpaid';
    case DEFAULT = 'pending';
}
