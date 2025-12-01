<?php

namespace App\Core\Application\DTO\Response\General;

use App\Core\Application\DTO\Response\PaymentConcept\PendingSummaryResponse;

/**
 * @OA\Schema(
 *     schema="DashboardDataResponse",
 *     type="object",
 *     @OA\Property(
 *         property="earnings",
 *         type="string",
 *         nullable=true,
 *         description="Ganancias totales",
 *         example="12500.50"
 *     ),
 *     @OA\Property(
 *         property="pending",
 *         ref="#/components/schemas/PendingSummaryResponse",
 *         nullable=true,
 *         description="Resumen de pagos pendientes"
 *     ),
 *     @OA\Property(
 *         property="students",
 *         type="integer",
 *         nullable=true,
 *         description="Cantidad de estudiantes",
 *         example=250
 *     )
 * )
 */

class DashboardDataResponse{

    public function __construct(
        public readonly ?string $earnings,
        public readonly ?PendingSummaryResponse $pending,
        public readonly ?int $students
    )
    {

    }
}
