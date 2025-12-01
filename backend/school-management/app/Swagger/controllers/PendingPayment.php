<?php

namespace App\Swagger\controllers;

class PendingPayment
{
/**
 * @OA\Get(
 *     path="/api/v1/pending-payments/{id}",
 *     tags={"Pending Payment"},
 *     summary="Obtener pagos pendientes del usuario autenticado",
 *     description="Devuelve todos los conceptos pendientes de pago del usuario logueado.",
 *     operationId="getUserPendingPayments",
 *     security={{"bearerAuth":{}}},
 *
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
 *         description="ID del children",
 *         required=true,
 *         @OA\Schema(type="integer", example=3)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Pagos pendientes obtenidos correctamente.",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(
 *                     property="pending_payments",
 *                     type="array",
 *                     @OA\Items(ref="#/components/schemas/PendingPaymentConceptsResponse")
 *                 )
 *             ),
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 nullable=true,
 *                 example="No hay pagos pendientes para el usuario."
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=401,
 *         description="No autorizado - Token inválido o ausente."
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Error de validación en los parámetros enviados."
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Error interno del servidor."
 *     )
 * )
 */
public function pending(){}


/**
 * @OA\Post(
 *     path="/api/v1/pending-payments",
 *     tags={"Pending Payment"},
 *     summary="Generar intento de pago para un concepto pendiente",
 *     description="Crea un intento de pago en Stripe (u otro proveedor) para el concepto indicado y devuelve la URL del checkout.",
 *     operationId="createPaymentIntent",
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\RequestBody(
 *         required=true,
 *         description="Datos necesarios para generar el intento de pago",
 *         @OA\JsonContent(
 *            ref="#/components/schemas/PayConceptRequest"
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=201,
 *         description="Intento de pago generado correctamente.",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(
 *                     property="url_checkout",
 *                     type="string",
 *                     example="https://checkout.stripe.com/pay/cs_test_a1b2c3d4e5"
 *                 )
 *             ),
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="El intento de pago se generó con éxito."
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=403,
 *         description="No está permitido realizar esta acción."
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Recurso no encontrado."
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Error de validación en los datos enviados."
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Error interno del servidor."
 *     )
 * )
 */
public function payConcept(){}


/**
 * @OA\Get(
 *     path="/api/v1/pending-payments/overdue/{id}",
 *     summary="Obtener pagos vencidos del usuario autenticado",
 *     description="Devuelve los pagos que ya están vencidos para el usuario autenticado.",
 *     operationId="getUserOverduePayments",
 *     tags={"Pending Payment"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="forceRefresh",
 *         in="query",
 *         description="Forzar actualización del caché (true/false)",
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
 *         description="Pagos vencidos obtenidos correctamente.",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(
 *                     property="pending_payments",
 *                     type="array",
 *                     @OA\Items(ref="#/components/schemas/PendingPaymentConceptsResponse")
 *                 )
 *             ),
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 nullable=true,
 *                 example="No hay pagos vencidos para el usuario."
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=401,
 *         description="No autorizado - Token inválido o ausente."
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Error de validación en los parámetros enviados."
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Error interno del servidor."
 *     )
 * )
 */
public function overdue(){}

}