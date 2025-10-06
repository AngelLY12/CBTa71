<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Services\PaymentSystem\Staff\DebtsService;
use Illuminate\Http\Request;

class DebtsController extends Controller
{

    protected DebtsService $debtsService;

    public function __construct(DebtsService $debtsService)
    {
        $this->debtsService=$debtsService;

    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->query('search', null);

        $pendingPayments = $this->debtsService->showAllpendingPayments($search);

        return response()->json([
            'success' => true,
            'data' => $pendingPayments,
            'message' => $pendingPayments->isEmpty() ? 'No hay pagos pendientes registrados.':null
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
   public function validatePayment(Request $request)
    {
        $request->validate([
            'search' => 'required|string',
            'payment_intent_id' => 'required|string',
        ]);

        $data = $this->debtsService->validatePayment(
            $request->input('search'),
            $request->input('payment_intent_id')
        );

        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => 'Pago validado correctamente.'
        ]);

    }
}
