<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class PasswordResetLinkController extends Controller
{
    /**
    * Handle an incoming password reset link request.
    *
    * @throws \Illuminate\Validation\ValidationException
    *
    * @OA\Post(
    *     path="/api/forgot-password",
    *     tags={"Auth"},
    *     summary="Enviar link para restablecer contraseña",
    *     description="Envía un correo con el enlace de restablecimiento de contraseña al email proporcionado.",
    *     operationId="sendPasswordResetLink",
    *     @OA\RequestBody(
    *         required=true,
    *         @OA\JsonContent(
    *             required={"email"},
    *             @OA\Property(property="email", type="string", format="email", example="usuario@mail.com")
    *         )
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="Enlace de restablecimiento enviado correctamente",
    *         @OA\JsonContent(@OA\Property(property="status", type="string", example="passwords.sent"))
    *     ),
    *     @OA\Response(response=422, description="Email no válido o usuario no encontrado")
    * )
    */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status != Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return response()->json(['status' => __($status)]);
    }
}
