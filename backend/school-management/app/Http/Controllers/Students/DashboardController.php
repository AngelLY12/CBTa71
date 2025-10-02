<?php

namespace App\Http\Controllers\Students;

use App\Services\PaymentSystem\Student\DashboardService;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

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
    public function index()
    {
         $user = Auth::user();
            $data = $this->dashboardService->getDashboardData($user);

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
    }

    public function pending()
    {
         $user = Auth::user();
            $data = $this->dashboardService->pendingPaymentAmount($user);

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
    }

    public function paid()
    {
        $user = Auth::user();
            $data = $this->dashboardService->paymentsMade($user);

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
    }

    public function overdue()
    {
        $user = Auth::user();
            $data = $this->dashboardService->overduePayments($user);

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
    }

    public function history()
    {
         $user = Auth::user();
            $data = $this->dashboardService->paymentHistory($user);

            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => $data->isEmpty()?'No hay pagos registrados en el historial':null

            ]);
    }
}
