<?php

namespace App\Http\Controllers\Students;

use App\Core\Application\Services\Payments\Student\PendingPaymentServiceFacades;
use App\Core\Infraestructure\Mappers\UserMapper;
use App\Exceptions\ConceptNotFoundException;
use App\Exceptions\StripeCheckoutSessionException;
use App\Http\Controllers\Controller;
use Http\Client\Exception\HttpException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class PendingPaymentController extends Controller
{

    protected PendingPaymentServiceFacades $pendingPaymentService;

    public function __construct(PendingPaymentServiceFacades $pendingPaymentService)
    {
        $this->pendingPaymentService= $pendingPaymentService;

    }

    public function index()
    {
        $user = Auth::user();
        $pending=$this->pendingPaymentService->showPendingPayments(UserMapper::toDomain($user));
        return response()->json([
            'success' => true,
            'data' => ['pending_payments'=>$pending],
            'message' => empty($pending) ? 'No hay pagos pendientes para el usuario.':null
        ]);

    }

    public function overdue()
    {
        $user = Auth::user();
        $pending=$this->pendingPaymentService->showOverduePayments(UserMapper::toDomain($user));
        return response()->json([
            'success' => true,
            'data' => ['overdue_payments'=>$pending],
            'message' => empty($pending) ? 'No hay pagos vencidos para el usuario.':null
        ]);

    }

    public function store(Request $request)
    {
             $user = Auth::user();
            $payment= $this->pendingPaymentService->payConcept(
                    UserMapper::toDomain($user),
                    $request->integer('concept_id')
                );
        return response()->json([
            'success'=>true,
            'data'=>['url_checkout'=>$payment],
            'message' => 'El intento de pago se genero con exito.',
        ], 201);
    }

}
