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
     *     description="Devuelve una lista paginada de pagos registrados, con opción de búsqueda y refresco de caché.",
     *     operationId="getPayments",
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Filtro de búsqueda opcional (por nombre, referencia u otros campos)",
     *         required=false,
     *         @OA\Schema(type="string", example="Juan Pérez")
     *     ),
     *     @OA\Parameter(
     *         name="perPage",
     *         in="query",
     *         description="Número de resultados por página (por defecto 15)",
     *         required=false,
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Número de página actual (por defecto 1)",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="forceRefresh",
     *         in="query",
     *         description="Forzar actualización de caché (true o false)",
     *         required=false,
     *         @OA\Schema(type="boolean", example=false)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de pagos obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="payments", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="date", type="string", example="2025-07-12"),
     *                         @OA\Property(property="concept", type="string", example="Inscripción"),
     *                         @OA\Property(property="amount", type="integer", example=2500),
     *                         @OA\Property(property="method", type="string", example="card"),
     *                         @OA\Property(property="fullName", type="string", example="Carlo Magno")
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", nullable=true, example="No hay pagos registrados.")
     *         )
     *     ),
     *
     *     security={{"bearerAuth":{}}}
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
