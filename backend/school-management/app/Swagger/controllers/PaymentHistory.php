<?php

namespace App\Swagger\controllers;

class PaymentHistory
{

/**
 * @OA\Get(
 *     path="/api/v1/history/{studentId?}",
 *     tags={"Payment History"},
 *     summary="Obtener historial de pagos del usuario autenticado",
 *     description="Devuelve el historial de pagos del usuario logueado, con soporte para paginación y cacheo.",
 *     operationId="getUserPaymentHistory",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *                          name="X-User-Role",
 *                          in="header",
 *                          required=false,
 *                          description="Rol requerido para este endpoint",
 *                          @OA\Schema(
 *                              type="string",
 *                              example="student|parent"
 *                          )
 *                      ),
 *                      @OA\Parameter(
 *                          name="X-User-Permission",
 *                          in="header",
 *                          required=false,
 *                          description="Permiso requerido para este endpoint",
 *                          @OA\Schema(
 *                               type="string",
 *                               example="view.payment.history"
 *                           )
 *                      ),
 *
 *     @OA\Parameter(
 *         name="perPage",
 *         in="query",
 *         description="Cantidad de registros por página (por defecto 15).",
 *         required=false,
 *         @OA\Schema(type="integer", example=15)
 *     ),
 *     @OA\Parameter(
 *         name="page",
 *         in="query",
 *         description="Número de página (por defecto 1).",
 *         required=false,
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Parameter(
 *         name="forceRefresh",
 *         in="query",
 *         description="Forzar actualización del caché (true o false).",
 *         required=false,
 *         @OA\Schema(type="boolean", example=false)
 *     ),
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID del children (opcional)",
 *         required=false,
 *         @OA\Schema(type="integer", example=3)
 *     ),
 *     @OA\Response(
 *          response=200,
 *          description="Historial de pagos obtenido correctamente",
 *          @OA\JsonContent(
 *              allOf={
 *                  @OA\Schema(ref="#/components/schemas/SuccessResponse"),
 *                  @OA\Schema(
 *                      @OA\Property(
 *                          property="data",
 *                          type="object",
 *                          @OA\Property(
 *                              property="payment_history",
 *                              allOf={
 *                                  @OA\Schema(ref="#/components/schemas/PaginatedResponse"),
 *                                  @OA\Schema(
 *                                      @OA\Property(
 *                                          property="items",
 *                                          type="array",
 *                                          @OA\Items(ref="#/components/schemas/PaymentDetailResponse")
 *                                      )
 *                                  )
 *                              }
 *                          )
 *                      )
 *                  )
 *              }
 *          )
 *      ),
 *      @OA\Response(response=401, description="No autenticado", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
 *      @OA\Response(response=422, description="Error de validación", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
 *      @OA\Response(response=403, description="No autorizado", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
 *      @OA\Response(response=404, description="No encontrado", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
 *      @OA\Response(response=429, description="Demasiadas solicitudes", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
 *      @OA\Response(response=500, description="Error interno", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
 * )
 */
public function history(){}

    /**
     * @OA\Get(
     *     path="/api/v1/history/payment/{id}",
     *     summary="Buscar pago por ID",
     *     description="Obtiene la información detallada de un pago específico mediante su identificador.",
     *     tags={"FindEntity"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *                          name="X-User-Role",
     *                          in="header",
     *                          required=false,
     *                          description="Rol requerido para este endpoint",
     *                          @OA\Schema(
     *                              type="string",
     *                              example="student|parent"
     *                          )
     *                      ),
     *                      @OA\Parameter(
     *                          name="X-User-Permission",
     *                          in="header",
     *                          required=false,
     *                          description="Permiso requerido para este endpoint",
     *                          @OA\Schema(
     *                               type="string",
     *                               example="view.payment"
     *                           )
     *                      ),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del pago a buscar",
     *         @OA\Schema(type="integer", example=5)
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="Pago encontrado correctamente",
     *          @OA\JsonContent(
     *              allOf={
     *                  @OA\Schema(ref="#/components/schemas/SuccessResponse"),
     *                  @OA\Schema(
     *                      @OA\Property(
     *                          property="data",
     *                          type="object",
     *                          @OA\Property(
     *                              property="payment",
     *                              ref="#/components/schemas/DomainPayment"
     *                          )
     *                      )
     *                  )
     *              }
     *          )
     *      ),
     *      @OA\Response(response=404, description="No encontrado", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *      @OA\Response(response=401, description="No autenticado", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *      @OA\Response(response=403, description="No autorizado", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *      @OA\Response(response=429, description="Demasiadas solicitudes", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *      @OA\Response(response=500, description="Error interno", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function getPayment(){}

}
