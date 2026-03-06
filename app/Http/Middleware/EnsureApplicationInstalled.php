<?php

namespace App\Http\Middleware;

use App\Services\InstallationStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApplicationInstalled
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
        if ($this->installationStatus->isInstalled()) {
            return $next($request);
        }

        $installerEnabled = (bool) config('install.enabled', true);
        if ($installerEnabled && $this->isAllowedBeforeInstall($request)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $installerEnabled
                    ? __('ui.setup.not_installed_yet')
                    : __('ui.setup.installer_disabled'),
            ], 503);
        }

        if ($installerEnabled) {
            return redirect()->route('install.index');
        }

        return response(__('ui.setup.installer_disabled'), 503);
    }

    private function isAllowedBeforeInstall(Request $request): bool
    {
        return $request->is('install')
            || $request->is('install/*')
            || $request->is('locale')
            || $request->is('build/*')
            || $request->is('branding/theme.css')
            || $request->is('branding/logo')
            || $request->is('branding/favicon')
            || $request->is('favicon.ico')
            || $request->is('manifest.webmanifest')
            || $request->is('sw.js')
            || $request->is('robots.txt')
            || $request->is('up');
    }
}
