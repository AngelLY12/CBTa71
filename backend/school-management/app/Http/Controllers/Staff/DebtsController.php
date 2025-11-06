<?php

namespace App\Http\Controllers\Staff;

use App\Core\Application\Services\Payments\Staff\DebtsServiceFacades;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Debts",
 *     description="Endpoints para la gestión y consulta de pagos pendinetes y validación de los mismos cuando haya un error de registro"
 * )
 */
class DebtsController extends Controller
{

    protected DebtsServiceFacades $debtsService;

    public function __construct(DebtsServiceFacades $debtsService)
    {
        $this->debtsService=$debtsService;

    }
    /**
     * @OA\Get(
     *     path="/api/v1/debts",
     *     summary="Listar pagos pendientes",
     *     description="Obtiene una lista paginada de todos los pagos pendientes registrados. Permite buscar por nombre o control del estudiante, paginar los resultados y forzar la actualización del caché.",
     *     tags={"Debts"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Texto de búsqueda para filtrar por CURP, email o número de control del estudiante.",
     *         required=false,
     *         @OA\Schema(type="string", example="example@gmail.com")
     *     ),
     *     @OA\Parameter(
     *         name="perPage",
     *         in="query",
     *         description="Número de elementos por página",
     *         required=false,
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Número de página actual",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="forceRefresh",
     *         in="query",
     *         description="Si es true, fuerza la actualización del caché",
     *         required=false,
     *         @OA\Schema(type="boolean", example=false)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de pagos pendientes obtenida correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="pending_payments",
     *                     allOf={
     *                         @OA\Schema(ref="#/components/schemas/PaginatedResponse"),
     *                         @OA\Schema(
     *                             @OA\Property(
     *                                 property="items",
     *                                 type="array",
     *                                 @OA\Items(ref="#/components/schemas/ConceptNameAndAmountResponse")
     *                             )
     *                         )
     *                     }
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", nullable=true, example=null)
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $search = $request->query('search', null);
        $perPage = $request->query('perPage', 15);
        $forceRefresh = filter_var($request->query('forceRefresh', false), FILTER_VALIDATE_BOOLEAN);
        $page = $request->query('page', 1);
        $pendingPayments = $this->debtsService->showAllpendingPayments($search, $perPage, $page, $forceRefresh);

        return response()->json([
            'success' => true,
            'data' => ['pending_payments'=>$pendingPayments],
            'message' => empty($pendingPayments) ? 'No hay pagos pendientes registrados.':null
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/debts/validate",
     *     summary="Validar un pago de Stripe",
     *     description="Valida un pago realizado en Stripe mediante el `payment_intent_id` y la búsqueda del estudiante.",
     *     tags={"Debts"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"search","payment_intent_id"},
     *             @OA\Property(property="search", type="string", example="LOYA030504HMSPXNA8"),
     *             @OA\Property(property="payment_intent_id", type="string", example="pi_3Q3u9YBvLxeAQs9T1zUzr3YH")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Pago validado correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="validated_payment",
     *                     ref="#/components/schemas/PaymentValidateResponse"
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Pago validado correctamente.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación en los datos enviados"
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Recurso no encontrado"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error de Stripe"
     *     )
     * )
     */
   public function validatePayment(Request $request)
    {
        $request->validate([
            'search' => 'required|string',
            'payment_intent_id' => 'required|string',
        ]);

        $data = $this->debtsService->validatePayment(
            $request->input('search'),
            $request->input('payment_intent_id')
        );

        return response()->json([
            'success' => true,
            'data' => ['validated_payment'=>$data],
            'message' => 'Pago validado correctamente.'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/debts/stripe-payments",
     *     summary="Obtener pagos desde Stripe",
     *     description="Obtiene todos los pagos registrados en Stripe asociados a un estudiante específico. Se puede filtrar por año y forzar actualización del caché.",
     *     tags={"Debts"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Stripe customer id del usuario",
     *         required=true,
     *         @OA\Schema(type="string", example="cu..")
     *     ),
     *     @OA\Parameter(
     *         name="year",
     *         in="query",
     *         description="Año específico de los pagos",
     *         required=false,
     *         @OA\Schema(type="integer", example=2025)
     *     ),
     *     @OA\Parameter(
     *         name="forceRefresh",
     *         in="query",
     *         description="Si es true, fuerza la actualización del caché",
     *         required=false,
     *         @OA\Schema(type="boolean", example=false)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Pagos obtenidos correctamente desde Stripe",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="payments",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/StripePaymentsResponse")
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Pagos obtenidos correctamente.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación en los parámetros enviados"
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Recurso no encontrado"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error de Stripe"
     *     )
     * )
     */
    public function getStripePayments(Request $request)
    {
        $request->validate([
            'search' => 'required|string',
            'year' => 'nullable|integer',
        ]);
        $search=$request->query('search');
        $year=$request->integer('year',null);
        $forceRefresh = filter_var($request->query('forceRefresh', false), FILTER_VALIDATE_BOOLEAN);

        $payments = $this->debtsService->getPaymentsFromStripe($search, $year, $forceRefresh);

        return response()->json([
            'success' => true,
            'data' => ['payments'=>$payments],
            'message' => 'Pagos obtenidos correctamente.'
        ]);
    }

}
