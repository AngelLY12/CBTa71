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
                'message' => empty($cards) ? 'No se encontraron métodos de pago.':null
            ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        try {
         $this->cardsService->savedPaymentMethod(
                $request->input('payment_method_id'),
                $user
            );
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo guardar el método de pago',
                'error' => $e->getMessage()
            ], 400);
        }


        return response()->json([
            'success'=>true,
            'message' => 'Método de pago guardado correctamente.',
        ], 201);

    }

   public function setupIntent()
{
        $user = Auth::user();
        $setupIntent = $this->cardsService->setupIntent($user);
        return response()->json([
            'success' => true,
            'client_secret' => $setupIntent->client_secret
        ]);

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
