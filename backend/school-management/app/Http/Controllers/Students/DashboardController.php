<?php

namespace App\Http\Controllers\Students;

use App\Core\Application\Services\Payments\Student\DashboardService;
use App\Core\Application\Services\Payments\Student\DashboardServiceFacades;
use App\Core\Infraestructure\Mappers\UserMapper;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Client\Request;


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
     *                 @OA\Property(property="statistics", type="object",
     *                     @OA\Property(property="completed", type="integer", example=1200),
     *                     @OA\Property(property="pending", type="object",
     *                           @OA\Property(property="total_amount", type="integer", example=2500),
     *                           @OA\Property(property="total_count", type="integer", example=2),
     *                      ),
     *                     @OA\Property(property="overdue", type="integer", example=105),
     *                 )
     *             )
     *         )
     *     ),
     * )
     */
    public function index(Request $request)
    {
         $user = Auth::user();
         $forceRefresh = filter_var($request->query('forceRefresh', false), FILTER_VALIDATE_BOOLEAN);

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
     *     @OA\Response(
     *         response=200,
     *         description="Total de pagos pendientes obtenido correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="total_amount", type="integer", example=1200),
     *                 @OA\Property(property="total_count", type="integer", format="float", example=2)
     *             )
     *         )
     *     )
     * )
     */

    public function pending(Request $request)
    {
         $user = Auth::user();
         $forceRefresh = filter_var($request->query('forceRefresh', false), FILTER_VALIDATE_BOOLEAN);

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
     *                 @OA\Property(property="total_paid", type="integer", example=3500.00)
     *             )
     *         )
     *     )
     * )
     */

    public function paid(Request $request)
    {
        $user = Auth::user();
        $forceRefresh = filter_var($request->query('forceRefresh', false), FILTER_VALIDATE_BOOLEAN);

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
     *         description="Monto total de pagos vencidos obtenido correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="overdue", type="object",
     *                 @OA\Property(property="total_overdue", type="integer", example=500)
     *             )
     *         )
     *     )
     * )
     */
    public function overdue(Request $request)
    {
        $user = Auth::user();
        $forceRefresh = filter_var($request->query('forceRefresh', false), FILTER_VALIDATE_BOOLEAN);

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
     *     summary="Obtener historial de pagos del usuario",
     *     description="Devuelve una lista paginada con el historial de pagos realizados por el usuario autenticado.",
     *     operationId="getPaymentHistory",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="perPage", in="query", description="Número de registros por página", @OA\Schema(type="integer", example=15)),
     *     @OA\Parameter(name="page", in="query", description="Número de página", @OA\Schema(type="integer", example=1)),
     *     @OA\Parameter(name="forceRefresh", in="query", description="Forzar actualización de caché", @OA\Schema(type="boolean", example=false)),
     *     @OA\Response(
     *         response=200,
     *         description="Historial de pagos obtenido correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="payment_history", type="array",
     *                     @OA\Items(type="object",
     *      *                  @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="concept", type="string", example="Inscripción Enero-Junio"),
     *                         @OA\Property(property="amount", type="number", format="float", example=1500.00),
     *                         @OA\Property(property="date", type="string", example="2025-03-01T10:20:30Z")
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", nullable=true, example="No hay pagos registrados en el historial")
     *         )
     *     )
     * )
     */
    public function history(Request $request)
    {
         $user = Auth::user();
         $forceRefresh = filter_var($request->query('forceRefresh', false), FILTER_VALIDATE_BOOLEAN);
         $perPage = $request->query('perPage', 15);
         $page    = $request->query('page', 1);
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
