<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class NewPasswordController extends Controller
{
    /**
    * Handle an incoming new password request.
    *
    * @throws \Illuminate\Validation\ValidationException
    *
    * @OA\Post(
    *     path="/api/reset-password",
    *     tags={"Auth"},
    *     summary="Actualizar contraseña usando token",
    *     description="Permite que un usuario actualice su contraseña usando el token enviado por correo.",
    *     operationId="resetPassword",
    *     @OA\RequestBody(
    *         required=true,
    *         @OA\JsonContent(
    *             required={"token","email","password","password_confirmation"},
    *             @OA\Property(property="token", type="string", example="token_generado_por_correo"),
    *             @OA\Property(property="email", type="string", format="email", example="usuario@mail.com"),
    *             @OA\Property(property="password", type="string", format="password", example="nuevaContrasena123"),
    *             @OA\Property(property="password_confirmation", type="string", format="password", example="nuevaContrasena123")
    *         )
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="Contraseña actualizada correctamente",
    *         @OA\JsonContent(@OA\Property(property="status", type="string", example="passwords.reset"))
    *     ),
    *     @OA\Response(response=422, description="Validación fallida o token inválido")
    * )
    */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->string('password')),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status != Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return response()->json(['status' => __($status)]);
    }
}
