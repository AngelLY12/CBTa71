<?php

namespace App\Http\Controllers\Students;

use App\Core\Application\Services\Payments\Student\PaymentHistoryService;
use App\Core\Infraestructure\Mappers\UserMapper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(
 *     name="Payment history",
 *     description="Endpoints relacionados con el historial de pagos del usuario"
 * )
 */
class PaymentHistoryController extends Controller
{
    protected PaymentHistoryService $paymentHistoryService;
    public function __construct(PaymentHistoryService $paymentHistoryService){
        $this->paymentHistoryService= $paymentHistoryService;

    }

        /**
     * @OA\Get(
     *     path="/api/v1/history",
     *     tags={"Payment History"},
     *     summary="Obtener historial de pagos del usuario autenticado",
     *     description="Devuelve el historial de pagos del usuario logueado, con soporte para paginación y cacheo.",
     *     operationId="getUserPaymentHistory",
     *     security={{"bearerAuth":{}}},
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
     *
     *     @OA\Response(
     *         response=200,
     *         description="Historial de pagos obtenido correctamente.",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="payment_history",
     *                     allOf={
     *                         @OA\Schema(ref="#/components/schemas/PaginatedResponse"),
     *                         @OA\Schema(
     *                             @OA\Property(
     *                                 property="items",
     *                                 type="array",
     *                                 @OA\Items(ref="#/components/schemas/PaymentDetailResponse")
     *                             )
     *                         )
     *                     }
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 nullable=true,
     *                 example="No hay historial de pagos para este usuario."
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
    public function index(Request $request)
    {
       $user = Auth::user();
       $perPage = $request->query('perPage', 15);
       $page    = $request->query('page', 1);
       $forceRefresh = filter_var($request->query('forceRefresh', false), FILTER_VALIDATE_BOOLEAN);
            $history=$this->paymentHistoryService->paymentHistory(UserMapper::toDomain($user), $perPage, $page, $forceRefresh);
            return response()->json([
                'success' => true,
                'data' => ['payment_history'=>$history],
                'message' => empty($history) ? 'No hay historial de pagos para este usuario.':null
            ]);

    }
}
