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
     *
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
     *             required={"concept_id"},
     *             @OA\Property(
     *                 property="concept_id",
     *                 type="integer",
     *                 description="ID del concepto pendiente a pagar",
     *                 example=12
     *             )
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
