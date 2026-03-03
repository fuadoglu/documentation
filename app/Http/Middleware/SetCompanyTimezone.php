<?php

namespace App\Http\Middleware;

use App\Models\BrandingSetting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class SetCompanyTimezone
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            if (! Schema::hasTable('branding_settings')) {
                return $next($request);
            }

            $fallbackTimezone = config('app.timezone', 'UTC');
            $configuredTimezone = BrandingSetting::current()->timezone;
            $timezone = is_string($configuredTimezone) && in_array($configuredTimezone, timezone_identifiers_list(), true)
                ? $configuredTimezone
                : $fallbackTimezone;

            config(['app.timezone' => $timezone]);
            date_default_timezone_set($timezone);
        } catch (\Throwable) {
            // Do not break app bootstrap if DB/config is not ready yet.
        }

        return $next($request);
    }
}
