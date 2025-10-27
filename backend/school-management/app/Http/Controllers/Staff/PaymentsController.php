<?php

namespace App\Http\Controllers\Staff;

use App\Core\Application\Services\Payments\Staff\PaymentsService;
use App\Http\Controllers\Controller;
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
            'data' => ['payments'=>$payments],
            'message' => empty($payments) ? 'No hay pagos registrados.':null
        ]);
    }


}
