<?php

namespace App\Http\Controllers\Students;

use App\Http\Controllers\Controller;
use App\Services\PaymentSystem\Student\PendingPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class PendingPaymentController extends Controller
{

    protected PendingPaymentService $pendingPaymentService;

    public function __construct(PendingPaymentService $pendingPaymentService)
    {
        $this->pendingPaymentService= $pendingPaymentService;

    }

    public function index()
    {
        $user = Auth::user();
        $pending=$this->pendingPaymentService->showPendingPayments($user);
        return response()->json([
            'success' => true,
            'data' => $pending,
            'message' => empty($pending) ? 'No hay pagos pendientes para el usuario.':null
        ]);

    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $payment= $this->pendingPaymentService->payConcept(
                $user,
                $request->input->integer('concept_id')
            );


        return response()->json([
            'success'=>true,
            'data'=>$payment,
            'message' => 'El intento de pago se genero con exito.',
        ], 201);

    }

}
