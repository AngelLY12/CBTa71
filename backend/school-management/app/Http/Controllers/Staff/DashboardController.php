<?php

namespace App\Http\Controllers\Staff;

use App\Core\Application\Services\Payments\Staff\DashboardServiceFacades;
use App\Http\Controllers\Controller;
use App\Http\Requests\Payments\Staff\AllConceptsRequest;
use App\Http\Requests\Payments\Staff\DashboardRequest;
use Illuminate\Support\Facades\Response;

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


    public function getData(DashboardRequest $request)
    {
        $data = $this->dashboardService->getData(
            $request->validated()['only_this_year'] ?? false,
            $request->validated()['forceRefresh'] ?? false
        );

        return Response::success(
            ['statistics' => $data]
        );
    }


    public function pendingPayments(DashboardRequest $request)
    {
        $data = $this->dashboardService->pendingPaymentAmount($request->validated()['only_this_year'] ?? false,
            $request->validated()['forceRefresh'] ?? false);

        return Response::success(
            ['total_pending' => $data]
        );
    }


    public function allStudents(DashboardRequest $request)
    {
        $count = $this->dashboardService->getAllStudents($request->validated()['only_this_year'] ?? false,
            $request->validated()['forceRefresh'] ?? false);

        return Response::success(
            ['total_students' => $count]
        );
    }


    public function paymentsMade(DashboardRequest $request)
    {
        $total = $this->dashboardService->paymentsMade($request->validated()['only_this_year'] ?? false,
            $request->validated()['forceRefresh'] ?? false);

        return Response::success(
            ['total_earning' => $total]
        );
    }


    public function allConcepts(AllConceptsRequest $request)
    {
        $validated = $request->validated();

        $concepts = $this->dashboardService->getAllConcepts(
            $validated['only_this_year'] ?? false,
            $validated['perPage'] ?? 15,
            $validated['page'] ?? 1,
            $validated['forceRefresh'] ?? false
        );

        return Response::success(
            ['concepts' => $concepts]
        );
    }
    
    public function refreshDashboard()
    {
        $this->dashboardService->refreshAll();
        return Response::success(
            null,
            'Dashboard cache limpiado con éxito'
        );
    }


}
