<?php

namespace App\Http\Middleware;

use Closure;

class SecureHeadersMiddleware
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '0');
        $response->headers->set(
            'Content-Security-Policy',
            "default-src 'self';
            img-src 'self' data: https:;
            script-src 'self' 'unsafe-inline' https://js.stripe.com https://m.stripe.network;
            style-src 'self' 'unsafe-inline';
            frame-src https://js.stripe.com https://hooks.stripe.com https://m.stripe.network;
            connect-src 'self' https://api.stripe.com https://m.stripe.network https://r.stripe.com https://q.stripe.com;"
        );

        $response->headers->set(
            'Strict-Transport-Security',
            'max-age=31536000; includeSubDomains'
        );
        $response->headers->set(
            'Referrer-Policy',
            'strict-origin-when-cross-origin'
        );
        $response->headers->set(
            'Permissions-Policy',
            'camera=(), microphone=(), geolocation=(), payment=(self)'
        );

        return $response;
    }
}
