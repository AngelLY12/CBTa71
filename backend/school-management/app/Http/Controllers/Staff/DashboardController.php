<?php

namespace App\Http\Controllers\Staff;

use App\Core\Application\Services\Payments\Staff\DashboardServiceFacades;
use App\Http\Controllers\Controller;
use App\Http\Requests\Payments\Staff\AllConceptsRequest;
use App\Http\Requests\Payments\Staff\DashboardRequest;

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
    public function getData(DashboardRequest $request)
    {
        $data = $this->dashboardService->getData(
            $request->validated()['only_this_year'] ?? false,
            $request->validated()['forceRefresh'] ?? false
        );

        return response()->json([
            'success' => true,
            'data' => ['statistics'=>$data]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/dashboard-staff/pending-payments",
     *     summary="Obtener cantidad y monto total de pagos pendientes",
     *     description="Devuelve el total de conceptos pendientes de pago, incluyendo cantidad y monto total. Se puede filtrar por el año actual y forzar la actualización del caché.",
     *     tags={"Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="only_this_year",
     *         in="query",
     *         description="Filtrar solo por el año actual",
     *         required=false,
     *         @OA\Schema(type="boolean", example=true)
     *     ),
     *     @OA\Parameter(
     *         name="forceRefresh",
     *         in="query",
     *         description="Forzar actualización del caché",
     *         required=false,
     *         @OA\Schema(type="boolean", example=false)
     *     ),
     *     @OA\Response(
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
    public function pendingPayments(DashboardRequest $request)
    {
        $data = $this->dashboardService->pendingPaymentAmount($request->validated()['only_this_year'] ?? false,
            $request->validated()['forceRefresh'] ?? false);

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
     *     ),
     *      @OA\Response(
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

    public function allStudents(DashboardRequest $request)
    {
        $count = $this->dashboardService->getAllStudents($request->validated()['only_this_year'] ?? false,
            $request->validated()['forceRefresh'] ?? false);

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
     *                 @OA\Property(property="total_earning", type="string", example=325000.00)
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

    public function paymentsMade(DashboardRequest $request)
    {
        $total = $this->dashboardService->paymentsMade($request->validated()['only_this_year'] ?? false,
            $request->validated()['forceRefresh'] ?? false);

        return response()->json([
            'success' => true,
            'data' => ['total_earning'=>$total]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/dashboard-staff/concepts",
     *     summary="Obtener todos los conceptos de pago",
     *     description="Devuelve una lista paginada de conceptos de pago visibles en el panel del personal. Permite filtrar por año actual y forzar actualización del caché.",
     *     tags={"Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="only_this_year",
     *         in="query",
     *         description="Si es true, filtra los conceptos al año actual",
     *         required=false,
     *         @OA\Schema(type="boolean", example=true)
     *     ),
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
     *         description="Número de página a obtener",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="forceRefresh",
     *         in="query",
     *         description="Si es true, fuerza actualización del caché",
     *         required=false,
     *         @OA\Schema(type="boolean", example=false)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de conceptos obtenida correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="concepts",
     *                     allOf={
     *                         @OA\Schema(ref="#/components/schemas/PaginatedResponse"),
     *                         @OA\Schema(
     *                             @OA\Property(
     *                                 property="items",
     *                                 type="array",
     *                                 @OA\Items(ref="#/components/schemas/ConceptsToDashboardResponse")
     *                             )
     *                         )
     *                     }
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", nullable=true)
     *         )
     *     )
     * )
     */
    public function allConcepts(AllConceptsRequest $request)
    {
        $validated = $request->validated();

        $concepts = $this->dashboardService->getAllConcepts(
            $validated['only_this_year'] ?? false,
            $validated['perPage'] ?? 15,
            $validated['page'] ?? 1,
            $validated['forceRefresh'] ?? false
        );

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
