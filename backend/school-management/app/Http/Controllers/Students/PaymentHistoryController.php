<?php

namespace App\Http\Controllers\Students;

use App\Core\Application\Services\Payments\Student\PaymentHistoryService;
use App\Core\Infraestructure\Mappers\UserMapper;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class PaymentHistoryController extends Controller
{
    protected PaymentHistoryService $paymentHistoryService;
    public function __construct(PaymentHistoryService $paymentHistoryService){
        $this->paymentHistoryService= $paymentHistoryService;

    }


    public function index()
    {
       $user = Auth::user();
            $history=$this->paymentHistoryService->paymentHistory(UserMapper::toDomain($user));
            return response()->json([
                'success' => true,
                'data' => ['payment_history'=>$history],
                'message' => empty($history) ? 'No hay historial de pagos para este usuario.':null
            ]);

    }


}
