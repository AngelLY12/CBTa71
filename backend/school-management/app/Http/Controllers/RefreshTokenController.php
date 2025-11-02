<?php

namespace App\Http\Controllers;

use App\Core\Application\Services\RefreshTokenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(
 *     name="Auth",
 *     description="Endpoints de autenticación: renovación y cierre de sesión"
 * )
 */
class RefreshTokenController extends Controller
{
    private RefreshTokenService $service;
    public function __construct(
        RefreshTokenService $service
    )
    {
        $this->service= $service;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/refresh-token",
     *     summary="Refrescar token de acceso",
     *     description="Recibe un token de actualización (refresh token) y devuelve un nuevo token de acceso válido. Tambien rota el refresh token",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"refresh_token"},
     *             @OA\Property(property="refresh_token", type="string", example="def50200fcdcb15b13e...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Token renovado con éxito",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user_tokens", type="object",
     *                     @OA\Property(property="access_token", type="string", example="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."),
     *                     @OA\Property(property="refresh_token", type="string", example="def50200fcdcb15b13e..."),
     *                 )
     *             ),
     *             @OA\Property(property="token_type", type="string", example="Bearer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Refresh token invalido",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Refresh token inválido.")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $request->validate(['refresh_token' => 'required|string']);
        $newToken =$this->service->refreshToken($request->refresh_token);

        return response()->json([
            'success'=>true,
            'data' => ['user_tokens'=>$newToken],
            'token_type' => 'Bearer'
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/logout",
     *     summary="Cerrar sesión",
     *     description="Cierra la sesión del usuario y revoca el refresh token proporcionado.",
     *     tags={"Auth"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="x-refresh-token",
     *         in="header",
     *         description="Refresh token asociado a la sesión",
     *         required=true,
     *         @OA\Schema(type="string", example="def50200fcdcb15b13e...")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Sesión cerrada exitosamente (sin contenido)"
     *     ),
     * )
     */
    public function logout(Request $request)
    {
        $user=Auth::user();
        $refreshToken = $request->header('x-refresh-token');
        $this->service->logout($user, $refreshToken);
        return response()->noContent();
    }
}
