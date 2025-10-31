<?php

namespace App\Http\Controllers\Staff;

use App\Core\Application\Services\Payments\Staff\DebtsServiceFacades;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DebtsController extends Controller
{

    protected DebtsServiceFacades $debtsService;

    public function __construct(DebtsServiceFacades $debtsService)
    {
        $this->debtsService=$debtsService;

    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->query('search', null);
        $perPage = $request->query('perPage', 15);
        $forceRefresh = filter_var($request->query('forceRefresh', false), FILTER_VALIDATE_BOOLEAN);
        $page = $request->query('page', 1);
        $pendingPayments = $this->debtsService->showAllpendingPayments($search, $perPage, $page, $forceRefresh);

        return response()->json([
            'success' => true,
            'data' => ['pending_payments'=>$pendingPayments],
            'message' => empty($pendingPayments) ? 'No hay pagos pendientes registrados.':null
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
            'data' => ['validated_payment'=>$data],
            'message' => 'Pago validado correctamente.'
        ]);
    }
    public function getStripePayments(Request $request)
    {
        $request->validate([
            'search' => 'required|string',
            'year' => 'nullable|integer',
        ]);
        $search=$request->input('search');
        $year=$request->integer('year',null);
        $forceRefresh = filter_var($request->query('forceRefresh', false), FILTER_VALIDATE_BOOLEAN);

        $payments = $this->debtsService->getPaymentsFromStripe($search, $year, $forceRefresh);

        return response()->json([
            'success' => true,
            'data' => ['payments'=>$payments],
            'message' => 'Pagos obtenidos correctamente.'
        ]);
    }

}
