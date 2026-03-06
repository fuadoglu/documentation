<?php

namespace App\Http\Middleware;

use App\Services\InstallationStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockInstallerWhenInstalled
{
    public function __construct(
        private readonly InstallationStatus $installationStatus,
    ) {
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! (bool) config('install.enabled', true)) {
            abort(404);
        }

        if (! $this->installationStatus->isInstalled()) {
            return $next($request);
        }

        if ($request->user()) {
            return redirect()->route('dashboard');
        }

        return redirect()->route('login');
    }
}
