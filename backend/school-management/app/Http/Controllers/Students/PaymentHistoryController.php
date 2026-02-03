<?php

namespace App\Http\Controllers\Students;

use App\Core\Application\Services\Payments\Student\PaymentHistoryService;
use App\Core\Infraestructure\Mappers\UserMapper;
use App\Http\Controllers\Controller;
use App\Http\Requests\General\PaginationRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

/**
 * @OA\Tag(
 *     name="Payment history",
 *     description="Endpoints relacionados con el historial de pagos del usuario"
 * )
 */
class PaymentHistoryController extends Controller
{
    protected PaymentHistoryService $paymentHistoryService;
    public function __construct(PaymentHistoryService $paymentHistoryService){
        $this->paymentHistoryService= $paymentHistoryService;

    }


    public function index(PaginationRequest $request, ?int $studentId=null)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $forceRefresh = $request->validated()['forceRefresh'] ?? false;
        $targetUser = $user->resolveTargetUser($studentId);

        if (!$targetUser) {
            return Response::error('Acceso no permitido', 403);
        }
        $perPage = $request->integer('perPage', 15);
        $page = $request->integer('page', 1);
        $history=$this->paymentHistoryService->paymentHistory(UserMapper::toDomain($targetUser), $perPage, $page, $forceRefresh);
        return Response::success(
            ['payment_history' => $history],
            empty($history->items) ? 'No hay historial de pagos para este usuario.' : null
        );

    }

    public function findPayment(int $id)
    {
        $payment=$this->paymentHistoryService->findPayment($id);
        return Response::success(['payment' => $payment], 'Pago encontrado.');

    }
}
