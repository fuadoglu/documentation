<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecureHeaders
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $response->headers->has('X-Frame-Options')) {
            $response->headers->set('X-Frame-Options', 'DENY');
        }

        if (! $response->headers->has('X-Content-Type-Options')) {
            $response->headers->set('X-Content-Type-Options', 'nosniff');
        }

        if (! $response->headers->has('Referrer-Policy')) {
            $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        }

        if (! $response->headers->has('Permissions-Policy')) {
            $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        }

        if (! $response->headers->has('Cross-Origin-Resource-Policy')) {
            $response->headers->set('Cross-Origin-Resource-Policy', 'same-origin');
        }

        if (! $response->headers->has('Content-Security-Policy')) {
            $response->headers->set('Content-Security-Policy', $this->contentSecurityPolicy());
        }

        if ($request->isSecure() && ! $response->headers->has('Strict-Transport-Security')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }

    private function contentSecurityPolicy(): string
    {
        $scriptSrc = ["'self'"];
        $styleSrc = ["'self'", 'https://fonts.bunny.net'];
        $fontSrc = ["'self'", 'https://fonts.bunny.net', 'data:'];
        $imgSrc = ["'self'", 'data:', 'blob:'];
        $connectSrc = ["'self'"];

        if (app()->environment('local')) {
            $scriptSrc[] = "'unsafe-inline'";
            $scriptSrc[] = "'unsafe-eval'";
            $styleSrc[] = "'unsafe-inline'";

            $devOrigins = array_unique(array_filter([
                (string) env('VITE_DEV_SERVER_URL'),
                'http://127.0.0.1:5173',
                'http://localhost:5173',
            ]));

            foreach ($devOrigins as $origin) {
                $scriptSrc[] = $origin;
                $styleSrc[] = $origin;
                $fontSrc[] = $origin;
                $imgSrc[] = $origin;
                $connectSrc[] = $origin;
                $connectSrc[] = preg_replace('/^http:/', 'ws:', $origin) ?: $origin;
                $connectSrc[] = preg_replace('/^https:/', 'wss:', $origin) ?: $origin;
            }
        }

        return implode('; ', [
            "default-src 'self'",
            "base-uri 'self'",
            "frame-ancestors 'none'",
            "form-action 'self'",
            "object-src 'none'",
            'script-src '.implode(' ', array_unique($scriptSrc)),
            'style-src '.implode(' ', array_unique($styleSrc)),
            'font-src '.implode(' ', array_unique($fontSrc)),
            'img-src '.implode(' ', array_unique($imgSrc)),
            'connect-src '.implode(' ', array_unique($connectSrc)),
        ]);
    }
}
