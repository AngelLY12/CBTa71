<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Services\PaymentSystem\Staff\PaymentsService;
use Illuminate\Http\Request;

class PaymentsController extends Controller
{

    protected PaymentsService $paymentsService;

    public function __construct(PaymentsService $paymentsService)
    {
        $this->paymentsService = $paymentsService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');

        $payments = $this->paymentsService->showAllPayments($search);

        return response()->json([
            'success' => true,
            'data' => $payments,
            'message' => $payments->isEmpty() ? 'No hay pagos registrados.':null
        ]);
    }


}
