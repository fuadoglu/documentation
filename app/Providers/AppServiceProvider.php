<?php

namespace App\Providers;

use App\Models\BrandingSetting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->environment('production') && str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        View::composer('*', function ($view): void {
            try {
                if (! Schema::hasTable('branding_settings')) {
                    return;
                }

                $view->with('branding', BrandingSetting::current());
            } catch (\Throwable) {
                // If DB is not reachable yet (fresh setup), do not block rendering.
            }
        });
    }
}
