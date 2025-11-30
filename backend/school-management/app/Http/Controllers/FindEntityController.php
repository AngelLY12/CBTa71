<?php

namespace App\Http\Controllers;

use App\Core\Application\Services\Misc\FindEntityServiceFacades;
use App\Http\Requests\General\ForceRefreshRequest;
use Illuminate\Support\Facades\Response;

/**
 * @OA\Tag(
 *     name="FindEntity",
 *     description="Operaciones para buscar usuarios, pagos y conceptos"
 * )
 */
class FindEntityController extends Controller
{
    private FindEntityServiceFacades $service;
    public function __construct(
        FindEntityServiceFacades $service
    )
    {
        $this->service=$service;
    }

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
    public function findConcept(int $id)
    {
        $concept=$this->service->findConcept($id);
        return Response::success(['concept' => $concept], 'Concepto encontrado.');

    }

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
    public function findPayment(int $id)
    {
        $payment=$this->service->findPayment($id);
        return Response::success(['payment' => $payment], 'Pago encontrado.');

    }

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
    public function findUser(ForceRefreshRequest $request)
    {
        $forceRefresh = $request->validated()['forceRefresh'] ?? false;
        $user=$this->service->findUser($forceRefresh);
        return Response::success(['user' => $user], 'Usuario encontrado.');

    }
}
