<?php

namespace App\Http\Middleware;

use App\Core\Domain\Utils\Validators\UserValidator;
use App\Core\Infraestructure\Mappers\UserMapper;
use App\Exceptions\Unauthorized\UserInactiveException;
use App\Jobs\CheckUserStatusJob;
use App\Jobs\ClearStudentCacheJob;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserStatus
{
    public function __construct()
    {}
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user=$request->user();
        try {
            UserValidator::ensureUserIsActive(UserMapper::toDomain($user));
        } catch (UserInactiveException $e) {
            $user->currentAccessToken()?->delete();
            $user->currentRefreshToken()?->delete();
            CheckUserStatusJob::dispatch($user);
            ClearStudentCacheJob::dispatch($user->id);
            throw $e;
        }
        return $next($request);

    }
}
