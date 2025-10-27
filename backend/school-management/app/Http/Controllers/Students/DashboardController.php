<?php

namespace App\Http\Controllers\Students;

use App\Core\Application\Services\Payments\Student\DashboardService;
use App\Core\Application\Services\Payments\Student\DashboardServiceFacades;
use App\Core\Infraestructure\Mappers\UserMapper;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

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
    public function index()
    {
         $user = Auth::user();

            $data = $this->dashboardService->getDashboardData(UserMapper::toDomain($user));

            return response()->json([
                'success' => true,
                'data' => ['statistics'=>$data]
            ]);
    }

    public function pending()
    {
         $user = Auth::user();
            $data = $this->dashboardService->pendingPaymentAmount(UserMapper::toDomain($user));

            return response()->json([
                'success' => true,
                'data' => ['total_pending'=>$data]
            ]);
    }

    public function paid()
    {
        $user = Auth::user();
            $data = $this->dashboardService->paymentsMade(UserMapper::toDomain($user));

            return response()->json([
                'success' => true,
                'paid' => ['total_paid'=>$data]
            ]);
    }

    public function overdue()
    {
        $user = Auth::user();
            $data = $this->dashboardService->overduePayments(UserMapper::toDomain($user));

            return response()->json([
                'success' => true,
                'overdue' => ['total_overdue'=>$data]
            ]);
    }

    public function history()
    {
         $user = Auth::user();
            $data = $this->dashboardService->paymentHistory(UserMapper::toDomain($user));

            return response()->json([
                'success' => true,
                'data' => ['payment_history'=>$data],
                'message' => empty($data)?'No hay pagos registrados en el historial':null

            ]);
    }
}
