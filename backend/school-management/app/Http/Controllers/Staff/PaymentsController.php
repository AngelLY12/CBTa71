<?php

namespace App\Http\Controllers\Staff;

use App\Core\Application\Services\Payments\Staff\PaymentsService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Payments",
 *     description="Endpoints para la gestión y consulta de pagos registrados"
 * )
 */
class PaymentsController extends Controller
{

    protected PaymentsService $paymentsService;

    public function __construct(PaymentsService $paymentsService)
    {
        $this->paymentsService = $paymentsService;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/payments",
     *     tags={"Payments"},
     *     summary="Obtener lista de pagos",
     *     description="Devuelve una lista paginada de pagos registrados, con opción de búsqueda por email, CURP o número de control, y posibilidad de forzar actualización del caché.",
     *     operationId="getPayments",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Filtro opcional para buscar por email, nombre o nombre de concepto de pago.",
     *         required=false,
     *         @OA\Schema(type="string", example="example@gmail.com")
     *     ),
     *     @OA\Parameter(
     *         name="perPage",
     *         in="query",
     *         description="Número de resultados por página (por defecto 15).",
     *         required=false,
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Número de página actual (por defecto 1).",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="forceRefresh",
     *         in="query",
     *         description="Forzar actualización de caché (true o false).",
     *         required=false,
     *         @OA\Schema(type="boolean", example=false)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Lista de pagos obtenida exitosamente.",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="payments",
     *                     allOf={
     *                         @OA\Schema(ref="#/components/schemas/PaginatedResponse"),
     *                         @OA\Schema(
     *                             @OA\Property(
     *                                 property="items",
     *                                 type="array",
     *                                 @OA\Items(ref="#/components/schemas/PaymentListItemResponse")
     *                             )
     *                         )
     *                     }
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", nullable=true, example="No hay pagos registrados.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación en los parámetros enviados."
     *     ),
     *      @OA\Response(
     *         response=409,
     *         description="Recurso no encontrado"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor."
     *     )
     * )
     */
    public function index(Request $request)
    {
        $search = $request->query('search', null);
        $perPage = $request->query('perPage', 15);
        $page    = $request->query('page', 1);
        $forceRefresh = filter_var($request->query('forceRefresh', false), FILTER_VALIDATE_BOOLEAN);
        $payments = $this->paymentsService->showAllPayments($search, $perPage, $page, $forceRefresh);

        return response()->json([
            'success' => true,
            'data' => ['payments'=>$payments],
            'message' => empty($payments) ? 'No hay pagos registrados.':null
        ]);
    }
}
