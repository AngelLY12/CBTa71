<?php

namespace App\Http\Controllers\Students;

use App\Core\Application\Services\Payments\Student\DashboardServiceFacades;
use App\Core\Infraestructure\Mappers\UserMapper;
use App\Http\Requests\Payments\Staff\DashboardRequest;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Http\Requests\General\ForceRefreshRequest;
use App\Http\Requests\General\PaginationRequest;
use Illuminate\Support\Facades\Response;

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

    public function index(DashboardRequest $request, ?int $id=null)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $forceRefresh = $request->validated()['forceRefresh'] ?? false;
        $onlyThisYear = $request->validated()['only_this_year'] ?? false;
        $targetUser = $user->resolveTargetUser($id);

        if (!$targetUser) {
            return Response::error('Acceso no permitido', 403);
        }
        $data = $this->dashboardService->getDashboardData($onlyThisYear,UserMapper::toDomain($targetUser), $forceRefresh);

        return Response::success(['statistics' => $data]);

    }

    public function pending(DashboardRequest $request, ?int $id=null)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $forceRefresh = $request->validated()['forceRefresh'] ?? false;
        $onlyThisYear = $request->validated()['only_this_year'] ?? false;
        $targetUser = $user->resolveTargetUser($id);

        if (!$targetUser) {
            return Response::error('Acceso no permitido', 403);
        }
        $data = $this->dashboardService->pendingPaymentAmount($onlyThisYear,UserMapper::toDomain($targetUser), $forceRefresh);

        return Response::success(['total_pending' => $data]);

    }



    public function paid(DashboardRequest $request, ?int $id=null)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $forceRefresh = $request->validated()['forceRefresh'] ?? false;
        $onlyThisYear = $request->validated()['only_this_year'] ?? false;
        $targetUser = $user->resolveTargetUser($id);

        if (!$targetUser) {
            return Response::error('Acceso no permitido', 403);
        }

        $data = $this->dashboardService->paymentsMade($onlyThisYear,UserMapper::toDomain($targetUser), $forceRefresh);

        return Response::success(['total_paid' => $data]);

    }


    public function overdue(DashboardRequest $request, ?int $id)
    {
       /** @var \App\Models\User $user */
        $user = Auth::user();
        $forceRefresh = $request->validated()['forceRefresh'] ?? false;
        $onlyThisYear = $request->validated()['only_this_year'] ?? false;
        $targetUser = $user->resolveTargetUser($id);

        if (!$targetUser) {
            return Response::error('Acceso no permitido', 403);
        }

        $data = $this->dashboardService->overduePayments($onlyThisYear,UserMapper::toDomain($user), $forceRefresh);

        return Response::success(['total_overdue' => $data]);

    }

    public function history(PaginationRequest $request, ?int $id=null)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $forceRefresh = $request->validated()['forceRefresh'] ?? false;
        $onlyThisYear = $request->validated()['only_this_year'] ?? false;
        $targetUser = $user->resolveTargetUser($id);

        if (!$targetUser) {
            return Response::error('Acceso no permitido', 403);
        }
        $perPage = $request->integer('perPage', 15);
        $page = $request->integer('page', 1);
        $data = $this->dashboardService->paymentHistory($onlyThisYear,UserMapper::toDomain($targetUser), $perPage, $page, $forceRefresh);

        return Response::success(
            ['payment_history' => $data],
            empty($data->items) ? 'No hay pagos registrados en el historial' : null
        );
    }


    public function refreshDashboard()
    {
        $this->dashboardService->refreshAll();
        return Response::success(null, 'Dashboard cache limpiado con éxito');

    }
}
