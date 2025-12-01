<?php

namespace App\Swagger\controllers;

class DashboardStudent
{
/**
 * @OA\Post(
 *     path="/api/v1/dashboard/refresh",
 *     tags={"Dashboard"},
 *     summary="Limpiar caché del dashboard",
 *     description="Limpia el caché de datos almacenados en el dashboard (estadísticas, pagos, etc.)",
 *     operationId="refreshDashboardCache",
 *     security={{"bearerAuth":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="Caché del dashboard limpiado con éxito",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Dashboard cache limpiado con éxito")
 *         )
 *     )
 * )
 */
public function refresh(){}


/**
 * @OA\Get(
 *     path="/api/v1/dashboard/history/{id}",
 *     tags={"Dashboard"},
 *     summary="Obtener historial de pagos del usuario autenticado",
 *     description="Devuelve una lista paginada con el historial de pagos realizados por el usuario autenticado. Permite forzar la actualización del caché.",
 *     operationId="getPaymentHistory",
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="perPage",
 *         in="query",
 *         description="Número de registros por página",
 *         required=false,
 *         @OA\Schema(type="integer", example=15)
 *     ),
 *     @OA\Parameter(
 *         name="page",
 *         in="query",
 *         description="Número de página",
 *         required=false,
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Parameter(
 *         name="forceRefresh",
 *         in="query",
 *         description="Forzar actualización de caché (true o false)",
 *         required=false,
 *         @OA\Schema(type="boolean", example=false)
 *     ),
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID del children",
 *         required=true,
 *         @OA\Schema(type="integer", example=3)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Historial de pagos obtenido correctamente",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="payment_history",
 *                     allOf={
 *                         @OA\Schema(ref="#/components/schemas/PaginatedResponse"),
 *                         @OA\Schema(
 *                             @OA\Property(
 *                                 property="items",
 *                                 type="array",
 *                                 @OA\Items(ref="#/components/schemas/PaymentHistoryResponse")
 *                             )
 *                         )
 *                     }
 *                 )
 *             ),
 *             @OA\Property(property="message", type="string", nullable=true, example="No hay pagos registrados en el historial")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=401,
 *         description="No autorizado - Token inválido o ausente"
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Error de validación en los parámetros enviados"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Error interno del servidor"
 *     )
 * )
 */
public function history(){}


/**
 * @OA\Get(
 *     path="/api/v1/dashboard/overdue/{id}",
 *     tags={"Dashboard"},
 *     summary="Obtener total de pagos vencidos del usuario",
 *     description="Devuelve el monto total de los pagos vencidos asociados al usuario autenticado.",
 *     operationId="getOverduePayments",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="forceRefresh",
 *         in="query",
 *         description="Forzar actualización de caché (true o false)",
 *         required=false,
 *         @OA\Schema(type="boolean", example=false)
 *     ),
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID del children",
 *         required=true,
 *         @OA\Schema(type="integer", example=3)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Cantidad de pagos vencidos obtenido correctamente",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="overdue", type="object",
 *                 @OA\Property(property="total_overdue", type="integer", example=5)
 *             )
 *         )
 *     )
 * )
 */
public function overdue(){}

/**
 * @OA\Get(
 *     path="/api/v1/dashboard/paid/{id}",
 *     tags={"Dashboard"},
 *     summary="Obtener total de pagos realizados por el usuario",
 *     description="Devuelve el monto total de pagos completados por el usuario autenticado.",
 *     operationId="getPaidAmount",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="forceRefresh",
 *         in="query",
 *         description="Forzar actualización de caché (true o false)",
 *         required=false,
 *         @OA\Schema(type="boolean", example=false)
 *     ),
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID del children",
 *         required=true,
 *         @OA\Schema(type="integer", example=3)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Monto total de pagos realizados obtenido correctamente",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="paid", type="object",
 *                 @OA\Property(property="total_paid", type="string", example=3500.00)
 *             )
 *         )
 *     )
 * )
 */
public function paid(){}


/**
 * @OA\Get(
 *     path="/api/v1/dashboard/pending/{id}",
 *     tags={"Dashboard"},
 *     summary="Obtener total de pagos pendientes del usuario",
 *     description="Devuelve la cantidad y monto total de los pagos pendientes del usuario autenticado.",
 *     operationId="getPendingPayments",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="forceRefresh",
 *         in="query",
 *         description="Forzar actualización de caché (true o false)",
 *         required=false,
 *         @OA\Schema(type="boolean", example=false)
 *     ),
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID del children",
 *         required=true,
 *         @OA\Schema(type="integer", example=3)
 *     ),
 *      @OA\Response(
 *         response=200,
 *         description="Totales de pagos pendientes obtenidos correctamente",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="total_pending", ref="#/components/schemas/PendingSummaryResponse")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="No autenticado",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="No estás autenticado.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Error interno del servidor",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Ocurrió un error al obtener los pagos pendientes.")
 *         )
 *     )
 * )
 */
public function pending(){}


/**
 * @OA\Get(
 *     path="/api/v1/dashboard/{id}",
 *     tags={"Dashboard"},
 *     summary="Obtener estadísticas generales del dashboard del usuario",
 *     description="Devuelve información resumida de los conceptos, pagos y deudas del usuario autenticado.",
 *     operationId="getDashboardData",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="forceRefresh",
 *         in="query",
 *         description="Forzar actualización de caché (true o false)",
 *         required=false,
 *         @OA\Schema(type="boolean", example=false)
 *     ),
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID del children",
 *         required=true,
 *         @OA\Schema(type="integer", example=3)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Datos del dashboard obtenidos correctamente",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="statistics", ref="#/components/schemas/DashboardDataResponse")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="No autenticado",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="No estás autenticado.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Error interno del servidor",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Ocurrió un error inesperado al obtener los datos.")
 *         )
 *     )
 * )
 */
public function data(){}


}

