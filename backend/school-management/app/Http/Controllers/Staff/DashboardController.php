<?php

namespace App\Http\Controllers\Staff;

use App\Core\Application\Services\Payments\Staff\DashboardServiceFacades;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected DashboardServiceFacades $dashboardService;

    public function __construct(DashboardServiceFacades $dashboardService)
    {
        $this->dashboardService = $dashboardService;


    }
    /**
     * Display a listing of the resource.
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
     * Obtener la cantidad y monto de pagos pendientes
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
     * Obtener total de estudiantes
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
     * Obtener monto total de pagos realizados
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
     * Obtener todos los conceptos de pago
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

    public function refreshDashboard()
    {
        $this->dashboardService->refreshAll();
        return response()->json([
            'success' => true,
            'message' => 'Dashboard cache limpiado con éxito'
        ]);
    }


}
