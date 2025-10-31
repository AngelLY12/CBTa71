<?php

namespace App\Http\Controllers\Students;

use App\Core\Application\Services\Payments\Student\PaymentHistoryService;
use App\Core\Infraestructure\Mappers\UserMapper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Auth;

class PaymentHistoryController extends Controller
{
    protected PaymentHistoryService $paymentHistoryService;
    public function __construct(PaymentHistoryService $paymentHistoryService){
        $this->paymentHistoryService= $paymentHistoryService;

    }


    public function index(Request $request)
    {
       $user = Auth::user();
       $perPage = $request->query('perPage', 15);
       $page    = $request->query('page', 1);
       $forceRefresh = filter_var($request->query('forceRefresh', false), FILTER_VALIDATE_BOOLEAN);
            $history=$this->paymentHistoryService->paymentHistory(UserMapper::toDomain($user), $perPage, $page, $forceRefresh);
            return response()->json([
                'success' => true,
                'data' => ['payment_history'=>$history],
                'message' => empty($history) ? 'No hay historial de pagos para este usuario.':null
            ]);

    }


}
