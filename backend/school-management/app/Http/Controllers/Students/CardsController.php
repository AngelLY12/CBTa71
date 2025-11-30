<?php

namespace App\Http\Controllers\Students;

use App\Core\Application\Services\Payments\Student\CardsServiceFacades;
use App\Core\Infraestructure\Mappers\UserMapper;
use App\Http\Controllers\Controller;
use App\Http\Requests\General\ForceRefreshRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

/**
 * @OA\Tag(
 *     name="Cards",
 *     description="Gestión de métodos de pago (tarjetas) asociados a los usuarios"
 * )
 */
class CardsController extends Controller
{
    protected CardsServiceFacades $cardsService;

    public function __construct(CardsServiceFacades $cardsService)
    {
        $this->cardsService=$cardsService;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/cards/{id}",
     *     tags={"Cards"},
     *     summary="Listar métodos de pago del usuario autenticado",
     *     description="Obtiene la lista de tarjetas o métodos de pago asociados al usuario autenticado. Permite forzar actualización del caché.",
     *     operationId="getUserCards",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="forceRefresh",
     *         in="query",
     *         description="Si es true, fuerza la actualización del caché.",
     *         required=false,
     *         @OA\Schema(type="boolean", example=false)
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del children",
     *         required=true,
     *         @OA\Schema(type="integer", example=3)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de métodos de pago obtenida correctamente.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="cards",
     *                     type="array",
     *                     description="Lista de métodos de pago del usuario",
     *                     @OA\Items(ref="#/components/schemas/DisplayPaymentMethodResponse")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 nullable=true,
     *                 example="No se encontraron métodos de pago."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autorizado - Token inválido o ausente"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor"
     *     )
     * )
     */
    public function index(ForceRefreshRequest $request, ?int $id=null)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $forceRefresh = $request->validated()['forceRefresh'] ?? false;
        $targetUser = $user->resolveTargetUser($id);

        if (!$targetUser) {
            return Response::error('Acceso no permitido', 403);
        }
        $cards = $this->cardsService->getUserPaymentMethods($targetUser->id, $forceRefresh);

        return Response::success(
            ['cards' => $cards],
            empty($cards) ? 'No se encontraron métodos de pago.' : null
        );
    }

    /**
     * @OA\Post(
     *     path="/api/v1/cards",
     *     tags={"Cards"},
     *     summary="Registrar un nuevo método de pago",
     *     description="Crea una sesión de Stripe Checkout para registrar una nueva tarjeta del usuario autenticado.",
     *     operationId="addUserCard",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=201,
     *         description="Sesión de registro de método de pago creada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(
     *                     property="cards",
     *                     type="array",
     *                     description="Url del checkout para agregar tarjeta",
     *                     @OA\Items(ref="#/components/schemas/SetupCardResponse")
     *                 )
     *             )
     *         )
     *     ),
     *
     * )
     */
    public function store()
    {
        $user = Auth::user();

        $session= $this->cardsService->setupCard(UserMapper::toDomain($user));

        return Response::success(
            ['url_checkout' => $session->url],
            null,
            201
        );

    }

    /**
     * @OA\Delete(
     *     path="/api/v1/cards/{paymentMethodId}",
     *     tags={"Cards"},
     *     summary="Eliminar un método de pago",
     *     description="Elimina un método de pago específico asociado al usuario autenticado.",
     *     operationId="deleteUserCard",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="paymentMethodId",
     *         in="path",
     *         description="ID del método de pago a eliminar (por ejemplo, 'pm_1P7E89AjcPzVqRkV')",
     *         required=true,
     *         @OA\Schema(type="string", example="pm_1P7E89AjcPzVqRkV")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Método de pago eliminado correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Método de pago eliminado correctamente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Método de pago no encontrado"
     *     ),
     *
     * )
     */
    public function destroy(string $paymentMethodId)
    {
        $user = Auth::user();
        $this->cardsService->deletePaymentMethod(UserMapper::toDomain($user),$paymentMethodId);

        return Response::success(
            null,
            'Método de pago eliminado correctamente'
        );
    }
}
