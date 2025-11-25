<?php

use Stripe\Exception\RateLimitException;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\CardException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use App\Exceptions\DomainException;
use App\Http\Middleware\SecureHeadersMiddleware;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/auth.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);
        $middleware->append(SecureHeadersMiddleware::class);

        $middleware->alias([
            'verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);

        //
    })
    ->withSchedule(function (Schedule $schedule){
        $schedule->command('backup:dispatch-create-backup-job')->dailyAt('02:00');
        $schedule->command('backup:clean')->dailyAt('02:30');
        $schedule->command('db:auto-restore')->dailyAt('03:00');
        $schedule->command('concepts:dispatch-finalize-job')->daily();
        $schedule->command('tokens:dispatch-clean-expired-tokens')->everyFourHours();
        $schedule->command('tokens:dispatch-clean-expired-refresh-tokens')->dailyAt('03:00');
        $schedule->command('users:dispatch-delete-users')->weekly()->at('00:00');
        $schedule->command('concepts:dispatch-delete-concepts')->weekly()->at('00:00');
        $schedule->command('invites:dispatch-clean-expired-invites-job')->weekly()->at('02:00');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
        $exceptions->render(function (DomainException $e, Request $request) {
            logger('Handler ejecutado con: ' . get_class($e));

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getStatusCode());
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
        return response()->json([
            'success' => false,
            'message' => 'No estás autenticado',
            ], 401);
        });

        $exceptions->render(function ($e, Request $request) {
            if ($e instanceof AuthorizationException || $e instanceof UnauthorizedException) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para realizar esta acción.',
                ], 403);
            }
        });

        $exceptions->render(function (QueryException $e, Request $request) {
            logger()->error('Database error: ' . $e->getMessage());
            if ($e->errorInfo[1] === 1062) {
                return response()->json([
                    'success' => false,
                    'message' => 'Registro duplicado, ya existe un usuario con esos datos.',
                    'errors' => $e->errorInfo,
                ], 409);
            }
            return response()->json([
                'success' => false,
                'message' => 'Error interno al procesar la base de datos.',
                'errors' => $e->errorInfo
            ], 500);
        });

        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            return response()->json([
                'success' => false,
                'message' => 'Recurso no encontrado',
            ], 404);
        });

        $exceptions->render(function (\InvalidArgumentException $e, Request $request) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        });

        $exceptions->render(function (CardException $e, Request $request) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        });

        $exceptions->render(function (RateLimitException $e, Request $request) {
            return response()->json([
                'success' => false,
                'message' => 'Demasiadas solicitudes a Stripe, intenta más tarde.',
            ], 429);
        });

        $exceptions->render(function (ApiErrorException $e, Request $request) {
            return response()->json([
                'success' => false,
                'message' => 'Error al comunicarse con Stripe, intenta más tarde.',
            ], 502);
        });

        $exceptions->render(function (ValidationException $e, Request $request) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $e->errors(),
            ], 400);
        });


        $exceptions->render(function (\Throwable $e, Request $request) {
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error inesperado',
                'error' => $e->getMessage(),
            ], 500);
        });

        $exceptions->shouldRenderJsonWhen(function (Request $request, $e) {
            return $request->is('api/*') || $request->expectsJson();
        });
    })->create();
