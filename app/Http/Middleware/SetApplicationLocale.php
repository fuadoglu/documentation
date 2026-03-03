<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetApplicationLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $supported = config('app.available_locales', ['az', 'en']);
        if ($supported === []) {
            $supported = ['az', 'en'];
        }

        $userLocale = $request->user()?->locale;
        $sessionLocale = $request->session()->get('locale');

        $locale = $userLocale ?: $sessionLocale ?: config('app.locale');

        if (! in_array($locale, $supported, true)) {
            $locale = config('app.locale');
        }

        app()->setLocale($locale);

        if ($request->session()->get('locale') !== $locale) {
            $request->session()->put('locale', $locale);
        }

        return $next($request);
    }
}
