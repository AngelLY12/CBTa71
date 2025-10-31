<?php

namespace App\Http\Controllers\Students;

use App\Core\Application\Services\Payments\Student\CardsService;
use App\Core\Application\Services\Payments\Student\CardsServiceFacades;
use App\Core\Infraestructure\Mappers\UserMapper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CardsController extends Controller
{
    protected CardsServiceFacades $cardsService;

    public function __construct(CardsServiceFacades $cardsService)
    {
        $this->cardsService=$cardsService;
    }

    public function index(Request $request)
    {
       $user = Auth::user();
        $forceRefresh = filter_var($request->query('forceRefresh', false), FILTER_VALIDATE_BOOLEAN);
        $cards = $this->cardsService->getUserPaymentMethods(UserMapper::toDomain($user), $forceRefresh);
        return response()->json([
            'success' => true,
            'data' => ['cards'=>$cards],
            'message' => empty($cards) ? 'No se encontraron métodos de pago.':null
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

         $session= $this->cardsService->setupCard(UserMapper::toDomain($user));


        return response()->json([
            'success'=>true,
            'data' => ['url_checkout'=>$session->url],
        ], 201);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $paymentMethodId)
    {
        $user = Auth::user();
        $this->cardsService->deletePaymentMethod(UserMapper::toDomain($user),$paymentMethodId);

            return response()->json([
                'success' => true,
                'message' => 'Método de pago eliminado correctamente'
            ]);
    }
}
