<?php

use Stripe\Exception\RateLimitException;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\CardException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Exceptions\DomainException;
use App\Http\Middleware\CheckUserStatus;
use App\Http\Middleware\LogUserAction;
use App\Http\Middleware\SecureHeadersMiddleware;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use App\Core\Domain\Enum\Exceptions\ErrorCode;

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
            'log.action' => LogUserAction::class,
            'user.status' => CheckUserStatus::class
        ]);

        //
    })
    ->withSchedule(function (Schedule $schedule){
        $schedule->command('payments:dispatch-reconcile-payments-job')->everyTwoHours()->withoutOverlapping(30)->onOneServer();
        $schedule->command('tokens:dispatch-clean-expired-tokens')->everyThreeHours()->withoutOverlapping(30)->onOneServer();
        $schedule->command('backup:clean')->dailyAt('23:50');
        $schedule->command('concepts:dispatch-finalize-job')->dailyAt('00:05')->withoutOverlapping(30)->onOneServer();
        $schedule->command('backup:dispatch-create-backup-job')->dailyAt('01:05') ->withoutOverlapping(120);
        $schedule->command('db:auto-restore')->dailyAt('02:25')->withoutOverlapping(30);
        $schedule->command('tokens:dispatch-clean-expired-refresh-tokens')->dailyAt('03:25');
        $schedule->command('users:dispatch-delete-users')->weekly()->at('04:25')->withoutOverlapping(30)->onOneServer();
        $schedule->command('concepts:dispatch-delete-concepts')->weekly()->at('05:25')->withoutOverlapping(30)->onOneServer();
        $schedule->command('invites:dispatch-clean-expired-invites-job')->weekly()->at('05:55');
        $schedule->command('app:dispatch-promote-students-job')
        ->dailyAt('21:50')
        ->when(function () {
            $today = now();
            $lastDay = $today->endOfMonth()->day;
            $daysBeforeEnd = $lastDay - $today->day;
            return $daysBeforeEnd <= 8
                && in_array($today->month, config('promotions.allowed_months'));
        })
            ->withoutOverlapping(120)
            ->onOneServer();
        $schedule->command('logs:dispatch-clean-older-logs-job')->quarterly()->withoutOverlapping(120);
        $schedule->command('app:dispatch-optimize-database-job')->quarterly()->withoutOverlapping()->onOneServer();
        $schedule->command('cache:dispatch-clean-cache-job')->cron('0 0 1 */3 *')->withoutOverlapping(30);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        $exceptions->render(function (DomainException $e, Request $request) {
            return Response::error($e->getMessage(), $e->getStatusCode(), null, $e->getErrorCode()->value);
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            $token = $request->bearerToken();
            if ($token) {
                $sanctumToken = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
                if ($sanctumToken && $sanctumToken->expires_at && $sanctumToken->expires_at->isPast()) {
                    return Response::error('Access token expirado', 401, null, ErrorCode::ACCESS_TOKEN_EXPIRED->value);
                }
            }
            $message = strtolower($e->getMessage());
            if (str_contains($message, 'expired') || str_contains($message, 'expira')) {
                return Response::error('Access token expirado', 401, null, ErrorCode::ACCESS_TOKEN_EXPIRED->value);
            }
            return Response::error('No autenticado', 401, null, ErrorCode::UNAUTHENTICATED->value);
        });

        $exceptions->render(function (AuthorizationException $e, Request $request) {
            return Response::error('No tienes permisos para realizar esta acción.', 403, null, ErrorCode::FORBIDDEN->value);
        });

        $exceptions->render(function (\Spatie\Permission\Exceptions\UnauthorizedException $e, Request $request) {
            return Response::error(
                'No tienes permisos para acceder a este recurso',
                403,
                null,
                ErrorCode::FORBIDDEN->value
            );
        });

        $exceptions->render(function (QueryException $e, Request $request) {
            if ($e->errorInfo[1] === 1062) {
                return Response::error('Registro duplicado, ya existe una entidad con esos datos.', 409, $e->errorInfo, ErrorCode::DUPLICATE_ENTRY->value);
            }
            return Response::error('Error interno al procesar la base de datos.', 500, $e->errorInfo, ErrorCode::DATABASE_ERROR->value);
        });

        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            return Response::error('Recurso no encontrado', 404, null, ErrorCode::NOT_FOUND->value);
        });

        $exceptions->render(function (\InvalidArgumentException $e, Request $request) {
            return Response::error($e->getMessage(), 422, null, ErrorCode::INVALID_ARGUMENT->value);
        });

        $exceptions->render(function (CardException $e, Request $request) {
            $stripeCode = $e->getStripeCode() ?: ErrorCode::CARD_ERROR->value;
            return Response::error($e->getMessage(), 422,null, $stripeCode);
        });

        $exceptions->render(function (RateLimitException $e, Request $request) {
            return Response::error('Demasiadas solicitudes a Stripe, intenta más tarde.', 429, null, ErrorCode::RATE_LIMIT_EXCEEDED->value);
        });

        $exceptions->render(function (\Illuminate\Http\Exceptions\ThrottleRequestsException  $e, Request $request) {
            $headers = $e->getHeaders();
            $retryAfter = $headers['Retry-After'] ?? null;
            $reset = $headers['X-RateLimit-Reset'] ?? null;

            $response = Response::error(
                'Has excedido el límite de solicitudes, intenta nuevamente en unos segundos',
                429,
                [
                    'retry_at'     => $retryAfter ? now()->addSeconds($retryAfter)->timestamp : null,
                    'available_in' => $retryAfter ? (int) $retryAfter : null,
                    'limit'        => $headers['X-RateLimit-Limit'] ?? null,
                    'remaining'    => $headers['X-RateLimit-Remaining'] ?? null,
                    'reset_at'     => $reset ? (int) $reset : null,
                ],
                ErrorCode::TOO_MANY_REQUESTS->value
            );

            return $response->withHeaders($headers);
        });

        $exceptions->render(function (ApiErrorException $e, Request $request) {
            $stripeCode = $e->getStripeCode() ?: ErrorCode::STRIPE_API_ERROR->value;
            return Response::error('Error al comunicarse con Stripe, intenta más tarde.', 502,
                null,
                $stripeCode
            );
        });

        $exceptions->render(function (ValidationException $e, Request $request) {
            return Response::error('Errores de validación', 422, $e->errors(),ErrorCode::VALIDATION_ERROR->value);
        });

        $exceptions->render(function (\Illuminate\Http\Exceptions\ThrottleRequestsException $e, Request $request) {
            $headers = $e->getHeaders();
            $retryAfter = $headers['Retry-After'] ?? 60;

            return Response::error(
                "Demasiadas solicitudes. Intenta de nuevo en {$retryAfter} segundos.",
                429,
                [
                    'retry_after' => $retryAfter,
                    'retry_after_seconds' => (int) $retryAfter,
                    'limit' => $headers['X-RateLimit-Limit'] ?? null,
                    'remaining' => $headers['X-RateLimit-Remaining'] ?? null,
                ],
                ErrorCode::RATE_LIMIT_EXCEEDED->value ?? 'RATE_LIMIT_EXCEEDED'
            );
        });

        $exceptions->render(function (\Throwable $e, Request $request) {
            logger()->error("[UNHANDLED] " . $e->getMessage(), [
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString(),
            ]);

            return Response::error(
                app()->isProduction()
                    ? 'Ocurrió un error inesperado'
                    : $e->getMessage(),
                500,
                null,
                ErrorCode::INTERNAL_SERVER_ERROR->value
            );
        });

        $exceptions->shouldRenderJsonWhen(function (Request $request, $e) {
            return $request->is('api/*') || $request->expectsJson();
        });
    })->create();
