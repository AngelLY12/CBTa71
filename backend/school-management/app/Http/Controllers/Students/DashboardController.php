<?php

namespace App\Http\Controllers\Students;

use App\Core\Application\Services\Payments\Student\DashboardService;
use App\Core\Application\Services\Payments\Student\DashboardServiceFacades;
use App\Core\Infraestructure\Mappers\UserMapper;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Client\Request;

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
    public function refreshDashboard()
    {
        $this->dashboardService->refreshAll();
        return response()->json([
            'success' => true,
            'message' => 'Dashboard cache limpiado con éxito'
        ]);
    }
}
