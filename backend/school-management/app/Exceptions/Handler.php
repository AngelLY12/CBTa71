<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Stripe\Exception\RateLimitException;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\CardException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use App\Exceptions\DomainException;

class Handler extends ExceptionHandler
{
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
        });
    }

    public function render($request, Throwable $e)
    {

        if ($e instanceof DomainException) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getStatusCode());
        }

        if ($e instanceof AuthenticationException) {
            return response()->json([
                'success' => false,
                'message' => 'No estás autenticado',
            ], 401);
        }
        if ($e instanceof AuthorizationException || $e instanceof UnauthorizedException) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para realizar esta acción.',
            ], 403);
        }

        if ($e instanceof QueryException) {
            logger()->error('Database error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno al procesar la base de datos.',
            ], 500);
        }

        if ($e instanceof ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'message' => 'Recurso no encontrado',
            ], 404);
        }

        if ($e instanceof \InvalidArgumentException) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
        if ($e instanceof CardException) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        if ($e instanceof RateLimitException) {
            return response()->json([
                'success' => false,
                'message' => 'Demasiadas solicitudes a Stripe, intenta más tarde.',
            ], 429);
        }

        if ($e instanceof ApiErrorException) {
            return response()->json([
                'success' => false,
                'message' => 'Error al comunicarse con Stripe, intenta más tarde.',
            ], 502);
        }

        return response()->json([
            'success' => false,
            'message' => 'Ocurrió un error inesperado',
            'error' => $e->getMessage(),
        ], 500);
    }
}
