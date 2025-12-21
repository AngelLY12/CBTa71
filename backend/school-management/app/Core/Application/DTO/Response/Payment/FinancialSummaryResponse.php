<?php

namespace App\Core\Application\DTO\Response\Payment;

/**
 * @OA\Schema(
 *     schema="FinancialSummaryResponse",
 *     type="object",
 *
 *     @OA\Property(
 *         property="totalPayments",
 *         type="string",
 *         description="Monto total bruto de pagos recibidos",
 *         example="250000.00"
 *     ),
 *
 *     @OA\Property(
 *         property="totalPayouts",
 *         type="string",
 *         description="Monto total neto recibido (payouts)",
 *         example="230000.00"
 *     ),
 *
 *     @OA\Property(
 *         property="totalFees",
 *         type="string",
 *         description="Monto total de comisiones cobradas",
 *         example="20000.00"
 *     ),
 *
 *     @OA\Property(
 *         property="paymentsBySemester",
 *          type="object",
 *          description="Pagos brutos agrupados por semestre",
 *          additionalProperties=@OA\Property (
 *          type="object",
 *          @OA\Property(
 *              property="total",
 *              type="string",
 *              example="45000.00"
 *          ),
 *          @OA\Property(
 *              property="months",
 *              type="array",
 *              @OA\Items(type="string", example="15000.00")
 *              )
 *          )
 *      ),
 *
 *     @OA\Property(
 *          property="payoutsBySemester",
 *          type="object",
 *          description="Payouts agrupados por semestre",
 *          additionalProperties=@OA\Property (
 *          type="object",
 *          @OA\Property(
 *              property="total",
 *              type="string",
 *              example="38000.00"
 *          ),
 *          @OA\Property(
 *              property="total_fee",
 *              type="string",
 *              example="2500.00"
 *          ),
 *          @OA\Property(
 *              property="months",
 *              type="array",
 *              @OA\Items(
 *                  type="object",
 *                  @OA\Property(property="amount", type="string", example="18000.00"),
 *                  @OA\Property(property="fee", type="string", example="1200.00")
 *              )
 *          )
 *        )
 *     ),
 *
 *     @OA\Property(
 *         property="totalBalanceAvailable",
 *         type="string",
 *         description="Balance total disponible actualmente",
 *         example="35000.00"
 *     ),
 *
 *     @OA\Property(
 *         property="totalBalancePending",
 *         type="string",
 *         description="Balance total pendiente actualmente",
 *         example="15000.00"
 *     ),
 *
 *     @OA\Property(
 *         property="availablePercentage",
 *         type="string",
 *         description="Porcentaje del balance disponible respecto al total de pagos",
 *         example="14.00"
 *     ),
 *
 *     @OA\Property(
 *         property="pendingPercentage",
 *         type="string",
 *         description="Porcentaje del balance pendiente respecto al total de pagos",
 *         example="6.00"
 *     ),
 *
 *     @OA\Property(
 *         property="netReceivedPercentage",
 *         type="string",
 *         description="Porcentaje neto recibido respecto al total de pagos",
 *         example="92.00"
 *     ),
 *
 *     @OA\Property(
 *         property="feePercentage",
 *         type="string",
 *         description="Porcentaje total de comisión respecto al total de pagos",
 *         example="8.00"
 *     ),
 *
 *     @OA\Property(
 *         property="totalBalanceAvailableBySource",
 *         type="object",
 *         description="Balance disponible agrupado por método de pago",
 *         example={
 *             "card":"25000.00",
 *             "oxxo":"6000.00",
 *             "transfer":"4000.00"
 *         }
 *     ),
 *
 *     @OA\Property(
 *         property="totalBalancePendingBySource",
 *         type="object",
 *         description="Balance pendiente agrupado por método de pago",
 *         example={
 *             "card":"9000.00",
 *             "oxxo":"3000.00",
 *             "transfer":"3000.00"
 *         }
 *     )
 * )
 */
class FinancialSummaryResponse
{
    public function __construct(
        public string $totalPayments,
        public string $totalPayouts,
        public string $totalFees,

        public array  $paymentsBySemester,
        public array  $payoutsBySemester,

        public string $totalBalanceAvailable = '0.00',
        public string $totalBalancePending  = '0.00',

        public string $availablePercentage  = '0.00',
        public string $pendingPercentage  = '0.00',
        public string $netReceivedPercentage = '0.00',
        public string $feePercentage = '0.00',

        public array  $totalBalanceAvailableBySource = [],
        public array  $totalBalancePendingBySource   = [],
    )
    {
    }
}
