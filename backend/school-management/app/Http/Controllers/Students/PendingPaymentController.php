<?php

namespace App\Http\Controllers\Students;

use App\Core\Application\Services\Payments\Student\PendingPaymentServiceFacades;
use App\Core\Infraestructure\Mappers\UserMapper;
use App\Exceptions\ConceptNotFoundException;
use App\Exceptions\StripeCheckoutSessionException;
use App\Http\Controllers\Controller;
use Http\Client\Exception\HttpException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


/**
 * @OA\Tag(
 *     name="Pending payment",
 *     description="Endpoints relacionados con el pago de conceptos pendientes y visualización"
 * )
 */
class PendingPaymentController extends Controller
{

    protected PendingPaymentServiceFacades $pendingPaymentService;

    public function __construct(PendingPaymentServiceFacades $pendingPaymentService)
    {
        $this->pendingPaymentService= $pendingPaymentService;

    }

     /**
     * @OA\Get(
     *     path="/api/v1/pending-payments",
     *     summary="Obtener pagos pendientes del usuario autenticado",
     *     description="Devuelve todos los conceptos pendientes de pago del usuario logueado.",
     *     operationId="getUserPendingPayments",
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
     *
     *     @OA\Response(
     *         response=200,
     *         description="Pagos pendientes obtenidos correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="pending_payments", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=12),
     *                         @OA\Property(property="concept_name", type="string", example="Mensualidad Octubre"),
     *                         @OA\Property(property="amount", type="integer", example=550),
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", nullable=true, example="No hay pagos pendientes para el usuario.")
     *         )
     *     ),
     * )
     */

    public function index(Request $request)
    {
        $user = Auth::user();
        $forceRefresh = filter_var($request->query('forceRefresh', false), FILTER_VALIDATE_BOOLEAN);
        $pending=$this->pendingPaymentService->showPendingPayments(UserMapper::toDomain($user), $forceRefresh);
        return response()->json([
            'success' => true,
            'data' => ['pending_payments'=>$pending],
            'message' => empty($pending) ? 'No hay pagos pendientes para el usuario.':null
        ]);

    }

    /**
     * @OA\Get(
     *     path="/api/v1/pending-payments/overdue",
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
     *
     *     @OA\Response(
     *         response=200,
     *         description="Pagos vencidos obtenidos correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="overdue_payments", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=5),
     *                         @OA\Property(property="concept_name", type="string", example="Mensualidad Septiembre"),
     *                         @OA\Property(property="amount", type="integer", example=600),
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", nullable=true, example="No hay pagos vencidos para el usuario.")
     *         )
     *     ),
     *     @OA\Response(response=401, description="No autorizado - Token inválido o ausente")
     * )
     */
    public function overdue(Request $request)
    {
        $user = Auth::user();
        $forceRefresh = filter_var($request->query('forceRefresh', false), FILTER_VALIDATE_BOOLEAN);
        $pending=$this->pendingPaymentService->showOverduePayments(UserMapper::toDomain($user), $forceRefresh);
        return response()->json([
            'success' => true,
            'data' => ['overdue_payments'=>$pending],
            'message' => empty($pending) ? 'No hay pagos vencidos para el usuario.':null
        ]);

    }

    /**
     * @OA\Post(
     *     path="/api/v1/pending-payments",
     *     summary="Generar intento de pago para un concepto pendiente",
     *     description="Crea un intento de pago en Stripe (u otro proveedor) para el concepto indicado.",
     *     operationId="createPaymentIntent",
     *     tags={"Pending Payment"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"concept_id"},
     *             @OA\Property(property="concept_id", type="integer", example=12, description="ID del concepto a pagar")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Intento de pago generado correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="url_checkout", type="string", example="https://checkout.stripe.com/pay/cs_test_...")
     *             ),
     *             @OA\Property(property="message", type="string", example="El intento de pago se generó con éxito.")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Error de validación"),
     *     @OA\Response(response=404, description="Recurso no encontrado"),
     *     @OA\Response(response=403, description="No esta permitido")
     * )
     */
    public function store(Request $request)
    {
             $user = Auth::user();
            $payment= $this->pendingPaymentService->payConcept(
                    UserMapper::toDomain($user),
                    $request->integer('concept_id')
                );
        return response()->json([
            'success'=>true,
            'data'=>['url_checkout'=>$payment],
            'message' => 'El intento de pago se genero con exito.',
        ], 201);
    }
}
