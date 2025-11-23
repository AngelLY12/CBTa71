<?php

namespace App\Http\Controllers;

use App\Core\Application\Services\RefreshTokenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
     *    @OA\Response(
     *         response=200,
     *         description="Tokens renovados con exito",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user_tokens", ref="#/components/schemas/LoginResponse")
     *             ),
     *             @OA\Property(property="message", type="string", example="Tokens renovados.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Error en la validación de datos",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 description="Listado de errores de validación",
     *                 example={
     *                     "refresh_token": {"El refresh token es obligatorio."},
     *                 }
     *             ),
     *             @OA\Property(property="message", type="string", example="Error en la validación de datos.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Credenciales incorrectas",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Credenciales incorrectas.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Error inesperado del servidor",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Ocurrió un error inesperado.")
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
     *     @OA\Response(
     *         response=422,
     *         description="Error en la validación de datos",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 description="Listado de errores de validación",
     *                 example={
     *                     "refresh_token": {"El refresh token es es invalido o incorrecto."},
     *                 }
     *             ),
     *             @OA\Property(property="message", type="string", example="Error en la validación de datos.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Credenciales incorrectas",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Credenciales incorrectas.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Error inesperado del servidor",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Ocurrió un error inesperado.")
     *         )
     *     )
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
