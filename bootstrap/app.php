<?php

use App\Http\Middleware\EnsureActiveUser;
use App\Http\Middleware\PreventSensitiveResponseCaching;
use App\Http\Middleware\SecureHeaders;
use App\Http\Middleware\SetCompanyTimezone;
use App\Http\Middleware\SetApplicationLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            SetCompanyTimezone::class,
            SetApplicationLocale::class,
            SecureHeaders::class,
        ]);

        $middleware->alias([
            'active' => EnsureActiveUser::class,
            'no-store' => PreventSensitiveResponseCaching::class,
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
