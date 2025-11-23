<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
    * Mark the authenticated user's email address as verified.
    *
    * @OA\Get(
    *     path="/api/verify-email/{id}/{hash}",
    *     tags={"Auth"},
    *     summary="Verificar email del usuario",
    *     description="Marca el correo del usuario como verificado si el hash es correcto.",
    *     operationId="verifyEmail",
    *     @OA\Parameter(
    *         name="id",
    *         in="path",
    *         required=true,
    *         description="ID del usuario",
    *         @OA\Schema(type="integer", example=1)
    *     ),
    *     @OA\Parameter(
    *         name="hash",
    *         in="path",
    *         required=true,
    *         description="Hash de verificación enviado por correo",
    *         @OA\Schema(type="string", example="hash_generado_por_laravel")
    *     ),
    *     @OA\Response(response=302, description="Redirige al frontend con ?verified=1"),
    *     @OA\Response(response=401, description="No autenticado"),
    *     security={{"bearerAuth":{}}}
    * )
    */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(
                config('app.frontend_url').'/dashboard?verified=1'
            );
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return redirect()->intended(
            config('app.frontend_url').'/dashboard?verified=1'
        );
    }
}
