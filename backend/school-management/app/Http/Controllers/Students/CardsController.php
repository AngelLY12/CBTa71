<?php

namespace App\Http\Controllers\Students;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\PaymentSystem\Student\CardsService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CardsController extends Controller
{
    protected CardsService $cardsService;

    public function __construct(CardsService $cardsService)
    {
        $this->cardsService=$cardsService;
    }

    public function index()
    {
       $user = Auth::user();
            $cards = $this->cardsService->showPaymentMethods($user);
            return response()->json([
                'success' => true,
                'data' => $cards,
                'message' => $cards->isEmpty() ? 'No se encontraron métodos de pago.':null
            ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

         $session= $this->cardsService->savedPaymentMethod($user);


        return response()->json([
            'success'=>true,
            'checkout_url' => $session->url,
        ], 201);

    }

    public function save(Request $request)
    {
        $user = Auth::user();
        $sessionId = $request->query('session_id');
        if (!$sessionId) {
            return response()->json(['success' => false, 'message' => 'session_id requerido'], 400);
        }

        $setupIntent = $this->cardsService->finalizeSetupFromSessionId($sessionId);

        if (!$setupIntent || empty($setupIntent->payment_method)) {
            return response()->json(['success' => false, 'message' => 'No se encontró payment_method en la sesión'], 404);
        }

        $paymentMethodId = $setupIntent->payment_method;
        $pm = $this->cardsService->getPaymentMethodDetails($user,$paymentMethodId);

        return response()->json([
            'success' => true,
            'payment_method_id' => $paymentMethodId,
            'card' => [
                'brand' => $pm->card->brand ?? null,
                'last4' => $pm->card->last4 ?? null,
                'exp_month' => $pm->card->exp_month ?? null,
                'exp_year' => $pm->card->exp_year ?? null,
            ]
        ], 200);
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $paymentMethodId)
    {
        $this->cardsService->deletePaymentMethod($paymentMethodId);

            return response()->json([
                'success' => true,
                'message' => 'Método de pago eliminado correctamente'
            ]);
    }
}
