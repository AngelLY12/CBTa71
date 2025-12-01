<?php

namespace App\Swagger\controllers;

class FindEntity
{
/**
 * @OA\Get(
 *     path="/api/v1/find/concept/{id}",
 *     summary="Buscar concepto de pago por ID",
 *     description="Obtiene la información de un concepto de pago específico mediante su identificador.",
 *     tags={"FindEntity"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID del concepto a buscar",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Concepto encontrado correctamente.",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="concept", ref="#/components/schemas/DomainPaymentConcept")
 *             ),
 *             @OA\Property(property="message", type="string", example="Concepto encontrado.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Concepto no encontrado",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="El concepto solicitado no existe.")
 *         )
 *     ),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="No autorizado"),
 *     @OA\Response(response=500, description="Error inesperado")
 * )
 */
public function getConcept(){}


/**
 * @OA\Get(
 *     path="/api/v1/find/payment/{id}",
 *     summary="Buscar pago por ID",
 *     description="Obtiene la información detallada de un pago específico mediante su identificador.",
 *     tags={"FindEntity"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID del pago a buscar",
 *         @OA\Schema(type="integer", example=5)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Pago encontrado correctamente.",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="payment", ref="#/components/schemas/DomainPayment")
 *             ),
 *             @OA\Property(property="message", type="string", example="Pago encontrado.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Pago no encontrado",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="El pago solicitado no existe.")
 *         )
 *     ),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="No autorizado"),
 *     @OA\Response(response=500, description="Error inesperado")
 * )
 */
public function getPayment(){}


}

