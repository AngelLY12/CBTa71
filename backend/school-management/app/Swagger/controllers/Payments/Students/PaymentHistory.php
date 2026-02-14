<?php

namespace App\Swagger\controllers\Payments\Students;

class PaymentHistory
{

/**
 * @OA\Get(
 *     path="/api/v1/payments/history/{studentId?}",
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
 *                               example="view.payments.history"
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
 *                                          @OA\Items(ref="#/components/schemas/PaymentHistoryResponse")
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
     *     path="/api/v1/payments/history/payment/{id}",
     *     summary="Buscar pago por ID",
     *     description="Obtiene la información detallada de un pago específico mediante su identificador.",
     *     tags={"Payment History"},
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
     *                               example="view.payments.history"
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
     *                              ref="#/components/schemas/PaymentToDisplay"
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


    /**
     * @OA\Get(
     *     path="/api/v1/payments/history/receipt/{paymentId}",
     *     summary="Descargar recibo de pago en PDF",
     *     description="Genera y descarga el recibo de un pago específico en formato PDF.",
     *     tags={"Receipts"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="X-User-Role",
     *         in="header",
     *         required=false,
     *         description="Rol requerido para este endpoint",
     *         @OA\Schema(
     *             type="string",
     *             example="student|parent"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="X-User-Permission",
     *         in="header",
     *         required=false,
     *         description="Permiso requerido para este endpoint",
     *         @OA\Schema(
     *             type="string",
     *             example="view.receipt"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="paymentId",
     *         in="path",
     *         required=true,
     *         description="ID del pago para generar el recibo",
     *         @OA\Schema(type="integer", example=123)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="PDF del recibo descargado correctamente",
     *         @OA\MediaType(
     *             mediaType="application/pdf",
     *             @OA\Schema(
     *                 type="string",
     *                 format="binary",
     *                 description="Archivo PDF del recibo"
     *             )
     *         ),
     *         @OA\Header(
     *             header="Content-Disposition",
     *             description="Indica que el archivo se descarga como attachment",
     *             @OA\Schema(type="string", example="attachment; filename=recibo-123.pdf")
     *         ),
     *         @OA\Header(
     *             header="Content-Type",
     *             description="Tipo de contenido del archivo",
     *             @OA\Schema(type="string", example="application/pdf")
     *         ),
     *         @OA\Header(
     *             header="Cache-Control",
     *             description="Control de caché",
     *             @OA\Schema(type="string", example="no-store, no-cache, must-revalidate, max-age=0")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Pago no encontrado o recibo no disponible",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Recibo no encontrado"),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autenticado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated"),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No autorizado - Permiso 'view.receipt' requerido",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized - Missing permission: view.receipt"),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno al generar o descargar el recibo",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error al generar el recibo"),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     )
     * )
     */
    public function getReceipt(){}

}
