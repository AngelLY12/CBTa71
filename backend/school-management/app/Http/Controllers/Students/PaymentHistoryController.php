<?php

namespace App\Http\Controllers\Students;

use App\Http\Controllers\Controller;
use App\Services\PaymentSystem\Student\PaymentHistoryService;
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
            $history=$this->paymentHistoryService->paymentHistory($user);
            return response()->json([
                'success' => true,
                'data' => $history,
                'message' => $history->isEmpty() ? 'No hay historial de pagos para este usuario.':null
            ]);

    }


}
