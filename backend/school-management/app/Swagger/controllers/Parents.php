<?php

namespace App\Swagger\controllers;

class Parents
{
/**
 *
 * @OA\Post(
 *     path="/api/parents/invite",
 *     summary="Enviar invitación a un padre",
 *     tags={"Parents"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(ref="#/components/schemas/SendInviteRequest")
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Invitación enviada",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Invitation enviada con exito"),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="token", type="string", example="uuid-token"),
 *                 @OA\Property(property="expires_at", type="string", format="date-time", example="2025-11-27T12:34:56Z")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Error de validación",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="El correo es obligatorio")
 *         )
 *     )
 * )
 */
public function invite(){}


/**
 *
 * @OA\Post(
 *     path="/api/parents/invite/accept",
 *     summary="Aceptar invitación de un padre",
 *     tags={"Parents"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(ref="#/components/schemas/AcceptInviteRequest")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Invitación aceptada",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="La invitación ha sido aceptada")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Error de validación",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="El token es obligatorio")
 *         )
 *     )
 * )
 */
public function accept(){}


/**
 *
 * @OA\Get(
 *     path="/api/parents/get-children",
 *     summary="Obtiene los hijos del parent",
 *     tags={"Parents"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID del parent",
 *         required=true,
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(
 *                     property="children",
 *                     type="array",
 *                     description="Lista de hijos del usuario",
 *                     @OA\Items(ref="#/components/schemas/ParentChildrenResponse")
 *                 )
 *             ),
 *             @OA\Property(property="message", type="string", example="Datos obtenidos")
 *         )
 *     ),
 *
 * )
 */
public function getChildren(){}

}

