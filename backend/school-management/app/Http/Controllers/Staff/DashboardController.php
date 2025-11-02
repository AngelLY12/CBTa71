<?php

namespace App\Http\Controllers\Staff;

use App\Core\Application\Services\Payments\Staff\DashboardServiceFacades;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Dashboard staff",
 *     description="Endpoints para la consulta de pagos, pendientes, conceptos y estudiantes"
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
     *     path="/api/v1/dashboard-staff/data",
     *     summary="Obtener estadísticas generales del dashboard",
     *     description="Devuelve estadísticas generales como totales, montos y datos de rendimiento. Se puede filtrar por año actual y forzar actualización del caché.",
     *     tags={"Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="only_this_year",
     *         in="query",
     *         description="Si es true, filtra los datos al año actual",
     *         required=false,
     *         @OA\Schema(type="boolean", example=true)
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
     *         description="Datos del dashboard obtenidos correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="statistics", type="object",
     *                      @OA\Property(property="students", type="integer", example=120),
     *                      @OA\Property(property="pending", type="object",
     *                           @OA\Property(property="total_amount", type="integer", example=2500),
     *                           @OA\Property(property="total_count", type="integer", example=2),
     *                      ),
     *                      @OA\Property(property="earnings", type="integer", example=1005),
     *
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getData(Request $request)
    {
        $onlyThisYear = filter_var($request->query('only_this_year', false), FILTER_VALIDATE_BOOLEAN);
        $forceRefresh = filter_var($request->query('forceRefresh', false), FILTER_VALIDATE_BOOLEAN);

        $data = $this->dashboardService->getData($onlyThisYear, $forceRefresh);

        return response()->json([
            'success' => true,
            'data' => ['statistics'=>$data]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/dashboard-staff/pending-payments",
     *     summary="Obtener cantidad y monto total de pagos pendientes",
     *     tags={"Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="only_this_year",
     *         in="query",
     *         description="Filtrar solo por el año actual",
     *         @OA\Schema(type="boolean", example=true)
     *     ),
     *     @OA\Parameter(
     *         name="forceRefresh",
     *         in="query",
     *         description="Forzar actualización del caché",
     *         @OA\Schema(type="boolean", example=false)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Totales de pagos pendientes obtenidos correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="total_pending", type="object", example={
     *                     "total_count": 12,
     *                     "total_amount": 5600.75
     *                 })
     *             )
     *         )
     *     )
     * )
     */
    public function pendingPayments(Request $request)
    {
        $onlyThisYear = filter_var($request->query('only_this_year', false), FILTER_VALIDATE_BOOLEAN);
        $forceRefresh = filter_var($request->query('forceRefresh', false), FILTER_VALIDATE_BOOLEAN);

        $data = $this->dashboardService->pendingPaymentAmount($onlyThisYear, $forceRefresh);

        return response()->json([
            'success' => true,
            'data' => ['total_pending'=>$data]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/dashboard-staff/students",
     *     summary="Obtener el número total de estudiantes",
     *     tags={"Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="only_this_year",
     *         in="query",
     *         description="Filtrar solo por el año actual",
     *         @OA\Schema(type="boolean", example=true)
     *     ),
     *     @OA\Parameter(
     *         name="forceRefresh",
     *         in="query",
     *         description="Forzar actualización del caché",
     *         @OA\Schema(type="boolean", example=false)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Número total de estudiantes obtenido correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="total_students", type="integer", example=1500)
     *             )
     *         )
     *     )
     * )
     */

    public function allStudents(Request $request)
    {
        $onlyThisYear = filter_var($request->query('only_this_year', false), FILTER_VALIDATE_BOOLEAN);
        $forceRefresh = filter_var($request->query('forceRefresh', false), FILTER_VALIDATE_BOOLEAN);

        $count = $this->dashboardService->getAllStudents($onlyThisYear, $forceRefresh);

        return response()->json([
            'success' => true,
            'data' => ['total_students'=>$count]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/dashboard-staff/payments-made",
     *     summary="Obtener monto total de pagos realizados",
     *     tags={"Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="only_this_year",
     *         in="query",
     *         description="Filtrar solo por el año actual",
     *         @OA\Schema(type="boolean", example=true)
     *     ),
     *     @OA\Parameter(
     *         name="forceRefresh",
     *         in="query",
     *         description="Forzar actualización del caché",
     *         @OA\Schema(type="boolean", example=false)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Monto total de pagos realizados obtenido correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="total_earning", type="number", example=325000.00)
     *             )
     *         )
     *     )
     * )
     */

    public function paymentsMade(Request $request)
    {
        $onlyThisYear = filter_var($request->query('only_this_year', false), FILTER_VALIDATE_BOOLEAN);
        $forceRefresh = filter_var($request->query('forceRefresh', false), FILTER_VALIDATE_BOOLEAN);

        $total = $this->dashboardService->paymentsMade($onlyThisYear, $forceRefresh);

        return response()->json([
            'success' => true,
            'data' => ['total_earning'=>$total]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/dashboard-staff/concepts",
     *     summary="Obtener todos los conceptos de pago",
     *     tags={"Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="only_this_year", in="query", @OA\Schema(type="boolean", example=true)),
     *     @OA\Parameter(name="perPage", in="query", @OA\Schema(type="integer", example=15)),
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer", example=1)),
     *     @OA\Parameter(name="forceRefresh", in="query", @OA\Schema(type="boolean", example=false)),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de conceptos obtenida correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="concepts", type="array",
     *                     @OA\Items(type="object", example={
     *                         "id": 1,
     *                         "concept_name": "Inscripción",
     *                         "amount": 1500,
     *                         "status": "activo"
     *                     })
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function allConcepts(Request $request)
    {
        $onlyThisYear = filter_var($request->query('only_this_year', false), FILTER_VALIDATE_BOOLEAN);
        $perPage = $request->query('perPage', 15);
        $page    = $request->query('page', 1);
        $forceRefresh = filter_var($request->query('forceRefresh', false), FILTER_VALIDATE_BOOLEAN);
        $concepts = $this->dashboardService->getAllConcepts($onlyThisYear, $perPage, $page, $forceRefresh);

        return response()->json([
            'success' => true,
            'data' => ['concepts'=>$concepts]
        ]);
    }
    /**
     * @OA\Post(
     *     path="/api/v1/dashboard-staff/refresh",
     *     summary="Limpiar el caché del dashboard",
     *     description="Forza el borrado del caché en todos los datos del dashboard.",
     *     tags={"Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Caché limpiado correctamente",
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
