<?php

namespace App\Http\Middleware;

use App\Jobs\LogUserActionJob;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogUserAction
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        register_shutdown_function(function () use ($request) {
            $user = $request->user();
            LogUserActionJob::dispatch(
                user:$user,
                request:[
                    'ip' =>$request->ip(),
                    'method' =>$request->method(),
                    'url' =>$request->fullUrl()
                ]
            )->onQueue('default');
        });

        return $response;
    }
}
