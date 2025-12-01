<?php

namespace App\Swagger\controllers;

class Users
{
/**
 * @OA\Patch(
 *     path="/api/v1/users/update",
 *     tags={"Users"},
 *     summary="Actualizar los datos generales de un usuario",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(ref="#/components/schemas/UpdateUserRequest")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Usuario actualizado correctamente",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="user", ref="#/components/schemas/DomainUser")
 *             ),
 *             @OA\Property(property="message", type="string", example="El usuario ha sido actualizado con éxito.")
 *         )
 *     )
 * )
 */
public function updateUser(){}


/**
 * @OA\Patch(
 *     path="/api/v1/users/update/password",
 *     tags={"Users"},
 *     summary="Actualizar la contraseña de un usuario",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             ref="#/components/schemas/UpdatePasswordRequest"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Contraseña actualizada correctamente",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Password actualizada con éxito")
 *         )
 *     )
 * )
 */
public function updatePassword(){}


/**
 * @OA\Get(
 *     path="/api/v1/users/user",
 *     summary="Obtener usuario autenticado",
 *     description="Devuelve la información del usuario autenticado en el sistema.",
 *     tags={"Users"},
 *     security={{"bearerAuth":{}}},
 *
 *      @OA\Parameter(
 *         name="forceRefresh",
 *         in="query",
 *         description="Forzar actualización del caché (true o false).",
 *         required=false,
 *         @OA\Schema(type="boolean", example=false)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Usuario autenticado encontrado correctamente.",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="user", ref="#/components/schemas/DomainUser")
 *             ),
 *             @OA\Property(property="message", type="string", example="Usuario encontrado.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Usuario no encontrado",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="No se encontró el usuario autenticado.")
 *         )
 *     ),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="No autorizado"),
 *     @OA\Response(response=500, description="Error inesperado")
 * )
 */
public function getUser(){}

}

