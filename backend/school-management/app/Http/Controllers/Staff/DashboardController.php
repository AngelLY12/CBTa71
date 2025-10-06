<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\PaymentSystem\Staff\DashboardService;

class DashboardController extends Controller
{
    protected DashboardService $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;


    }
    /**
     * Display a listing of the resource.
     */
    public function getData(Request $request)
    {
        $onlyThisYear = filter_var($request->query('only_this_year', false), FILTER_VALIDATE_BOOLEAN);

        $data = $this->dashboardService->getData($onlyThisYear);

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Obtener la cantidad y monto de pagos pendientes
     */
    public function pendingPayments(Request $request)
    {
        $onlyThisYear = filter_var($request->query('only_this_year', false), FILTER_VALIDATE_BOOLEAN);

        $data = $this->dashboardService->pendingPaymentAmount($onlyThisYear);

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Obtener total de estudiantes
     */
    public function allStudents(Request $request)
    {
        $onlyThisYear = filter_var($request->query('only_this_year', false), FILTER_VALIDATE_BOOLEAN);

        $count = $this->dashboardService->getAllStudents($onlyThisYear);

        return response()->json([
            'success' => true,
            'total_students' => $count
        ]);
    }

    /**
     * Obtener monto total de pagos realizados
     */
    public function paymentsMade(Request $request)
    {
        $onlyThisYear = filter_var($request->query('only_this_year', false), FILTER_VALIDATE_BOOLEAN);

        $total = $this->dashboardService->paymentsMade($onlyThisYear);

        return response()->json([
            'success' => true,
            'total_payments' => $total
        ]);
    }

    /**
     * Obtener todos los conceptos de pago
     */
    public function allConcepts(Request $request)
    {
        $onlyThisYear = filter_var($request->query('only_this_year', false), FILTER_VALIDATE_BOOLEAN);

        $concepts = $this->dashboardService->getAllConcepts($onlyThisYear);

        return response()->json([
            'success' => true,
            'concepts' => $concepts
        ]);
    }
}
