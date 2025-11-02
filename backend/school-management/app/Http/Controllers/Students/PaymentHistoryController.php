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
     *     summary="Obtener historial de pagos del usuario autenticado",
     *     description="Devuelve el historial de pagos del usuario logueado, con soporte para paginación y cacheo.",
     *     operationId="getUserPaymentHistory",
     *     tags={"Payment History"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="perPage",
     *         in="query",
     *         description="Cantidad de registros por página",
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
     *         description="Forzar actualización del caché (true/false)",
     *         required=false,
     *         @OA\Schema(type="boolean", example=false)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Historial de pagos obtenido correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="payment_history", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="concept_name", type="string", example="Inscripción"),
     *                         @OA\Property(property="amount", type="number", format="float", example=500.75),
     *                         @OA\Property(property="status", type="string", example="completed"),
     *                         @OA\Property(property="date", type="string", format="date-time", example="2025-10-31T14:30:00Z"),
     *                         @OA\Property(property="reference", type="string", example="pi_Hyg76..."),
     *                         @OA\Property(property="url", type="string", example="https://stripe..."),
     *                         @OA\Property(property="payment_method_details", type="array",
     *                          @OA\Items(
     *                               type="object",
     *                               @OA\Property(property="type", type="string", example="card"),
     *                               @OA\Property(property="brand", type="string", example="visa"),
     *                               @OA\Property(property="last4", type="string", example="4242")
     *                               )
     *                          )
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", nullable=true, example="No hay historial de pagos para este usuario.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autorizado - Token inválido o ausente"
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
