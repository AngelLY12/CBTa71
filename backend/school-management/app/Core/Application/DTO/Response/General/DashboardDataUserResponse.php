<?php

namespace App\Core\Application\DTO\Response\General;

use App\Core\Application\DTO\Response\PaymentConcept\PendingSummaryResponse;

/**
 * @OA\Schema(
 *     schema="DashboardDataUserResponse",
 *     type="object",
 *     @OA\Property(
 *         property="completed",
 *         type="string",
 *         nullable=true,
 *         description="Monto completado",
 *         example="10500.00"
 *     ),
 *     @OA\Property(
 *         property="pending",
 *         ref="#/components/schemas/PendingSummaryResponse",
 *         nullable=true,
 *         description="Resumen de pagos pendientes"
 *     ),
 *     @OA\Property(
 *         property="overdue",
 *         ref="#/components/schemas/PendingSummaryResponse",
 *         nullable=true,
 *         description="Cantidad de pagos vencidos",
 *     )
 * )
 */

class DashboardDataUserResponse
{
     public function __construct(
        public readonly ?string $completed,
        public readonly ?PendingSummaryResponse $pending,
        public readonly ?PendingSummaryResponse $overdue
    )
    {

    }
}
