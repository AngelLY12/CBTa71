<?php

namespace App\Http\Controllers\Students;

use App\Core\Application\Services\Payments\Student\DashboardServiceFacades;
use App\Core\Infraestructure\Mappers\UserMapper;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Http\Requests\General\ForceRefreshRequest;
use App\Http\Requests\General\PaginationRequest;


/**
 * @OA\Tag(
 *     name="Dashboard",
 *     description="Endpoints relacionados con el panel de control del usuario (estadísticas, pagos y resumen financiero)"
 * )
 */

class DashboardController extends Controller
{

    protected DashboardServiceFacades $dashboardService;

    public function __construct(DashboardServiceFacades $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/dashboard",
     *     tags={"Dashboard"},
     *     summary="Obtener estadísticas generales del dashboard del usuario",
     *     description="Devuelve información resumida de los conceptos, pagos y deudas del usuario autenticado.",
     *     operationId="getDashboardData",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="forceRefresh",
     *         in="query",
     *         description="Forzar actualización de caché (true o false)",
     *         required=false,
     *         @OA\Schema(type="boolean", example=false)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Datos del dashboard obtenidos correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="statistics", ref="#/components/schemas/DashboardDataResponse")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autenticado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No estás autenticado.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Ocurrió un error inesperado al obtener los datos.")
     *         )
     *     )
     * )
     */
    public function index(ForceRefreshRequest $request)
    {
        $user = Auth::user();
        $forceRefresh = $request->validated()['forceRefresh'] ?? false;

        $data = $this->dashboardService->getDashboardData(UserMapper::toDomain($user), $forceRefresh);

        return response()->json([
            'success' => true,
            'data' => ['statistics'=>$data]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/dashboard/pending",
     *     tags={"Dashboard"},
     *     summary="Obtener total de pagos pendientes del usuario",
     *     description="Devuelve la cantidad y monto total de los pagos pendientes del usuario autenticado.",
     *     operationId="getPendingPayments",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="forceRefresh",
     *         in="query",
     *         description="Forzar actualización de caché (true o false)",
     *         required=false,
     *         @OA\Schema(type="boolean", example=false)
     *     ),
     *      @OA\Response(
     *         response=200,
     *         description="Totales de pagos pendientes obtenidos correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="total_pending", ref="#/components/schemas/PendingSummaryResponse")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autenticado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No estás autenticado.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Ocurrió un error al obtener los pagos pendientes.")
     *         )
     *     )
     * )
     */

    public function pending(ForceRefreshRequest $request)
    {
        $user = Auth::user();
        $forceRefresh = $request->validated()['forceRefresh'] ?? false;

        $data = $this->dashboardService->pendingPaymentAmount(UserMapper::toDomain($user), $forceRefresh);

        return response()->json([
            'success' => true,
            'data' => ['total_pending'=>$data]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/dashboard/paid",
     *     tags={"Dashboard"},
     *     summary="Obtener total de pagos realizados por el usuario",
     *     description="Devuelve el monto total de pagos completados por el usuario autenticado.",
     *     operationId="getPaidAmount",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="forceRefresh",
     *         in="query",
     *         description="Forzar actualización de caché (true o false)",
     *         required=false,
     *         @OA\Schema(type="boolean", example=false)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Monto total de pagos realizados obtenido correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="paid", type="object",
     *                 @OA\Property(property="total_paid", type="string", example=3500.00)
     *             )
     *         )
     *     )
     * )
     */

    public function paid(ForceRefreshRequest $request)
    {
        $user = Auth::user();
        $forceRefresh = $request->validated()['forceRefresh'] ?? false;

        $data = $this->dashboardService->paymentsMade(UserMapper::toDomain($user), $forceRefresh);

        return response()->json([
            'success' => true,
            'paid' => ['total_paid'=>$data]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/dashboard/overdue",
     *     tags={"Dashboard"},
     *     summary="Obtener total de pagos vencidos del usuario",
     *     description="Devuelve el monto total de los pagos vencidos asociados al usuario autenticado.",
     *     operationId="getOverduePayments",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="forceRefresh",
     *         in="query",
     *         description="Forzar actualización de caché (true o false)",
     *         required=false,
     *         @OA\Schema(type="boolean", example=false)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cantidad de pagos vencidos obtenido correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="overdue", type="object",
     *                 @OA\Property(property="total_overdue", type="integer", example=5)
     *             )
     *         )
     *     )
     * )
     */
    public function overdue(ForceRefreshRequest $request)
    {
        $user = Auth::user();
        $forceRefresh = $request->validated()['forceRefresh'] ?? false;

        $data = $this->dashboardService->overduePayments(UserMapper::toDomain($user), $forceRefresh);

        return response()->json([
            'success' => true,
            'overdue' => ['total_overdue'=>$data]
        ]);
    }
    /**
     * @OA\Get(
     *     path="/api/v1/dashboard/history",
     *     tags={"Dashboard"},
     *     summary="Obtener historial de pagos del usuario autenticado",
     *     description="Devuelve una lista paginada con el historial de pagos realizados por el usuario autenticado. Permite forzar la actualización del caché.",
     *     operationId="getPaymentHistory",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="perPage",
     *         in="query",
     *         description="Número de registros por página",
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
     *         description="Forzar actualización de caché (true o false)",
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
     *                 @OA\Property(property="payment_history",
     *                     allOf={
     *                         @OA\Schema(ref="#/components/schemas/PaginatedResponse"),
     *                         @OA\Schema(
     *                             @OA\Property(
     *                                 property="items",
     *                                 type="array",
     *                                 @OA\Items(ref="#/components/schemas/PaymentHistoryResponse")
     *                             )
     *                         )
     *                     }
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", nullable=true, example="No hay pagos registrados en el historial")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="No autorizado - Token inválido o ausente"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación en los parámetros enviados"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor"
     *     )
     * )
     */
    public function history(PaginationRequest $request)
    {
        $user = Auth::user();
        $forceRefresh = $request->boolean('forceRefresh');
        $perPage = $request->integer('perPage', 15);
        $page = $request->integer('page', 1);
        $data = $this->dashboardService->paymentHistory(UserMapper::toDomain($user), $perPage, $page, $forceRefresh);

        return response()->json([
            'success' => true,
            'data' => ['payment_history'=>$data],
            'message' => empty($data)?'No hay pagos registrados en el historial':null

        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/dashboard/refresh",
     *     tags={"Dashboard"},
     *     summary="Limpiar caché del dashboard",
     *     description="Limpia el caché de datos almacenados en el dashboard (estadísticas, pagos, etc.)",
     *     operationId="refreshDashboardCache",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Caché del dashboard limpiado con éxito",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Dashboard cache limpiado con éxito")
     *         )
     *     )
     * )
     */
    public function refreshDashboard()
    {
        $this->dashboardService->refreshAll();
        return response()->json([
            'success' => true,
            'message' => 'Dashboard cache limpiado con éxito'
        ]);
    }
}
