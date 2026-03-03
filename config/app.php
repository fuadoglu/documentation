<?php

return [

    'name' => env('APP_NAME', 'ECO DC'),

    'env' => env('APP_ENV', 'production'),

    'debug' => (bool) env('APP_DEBUG', false),

    'url' => env('APP_URL', 'http://localhost'),

    'timezone' => env('APP_TIMEZONE', 'UTC'),

    'locale' => env('APP_LOCALE', 'az'),

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),

    'available_locales' => array_values(array_filter(array_map(
        static fn (string $locale): string => trim($locale),
        explode(',', (string) env('APP_AVAILABLE_LOCALES', 'az,en'))
    ))),

    'faker_locale' => env('APP_FAKER_LOCALE', 'en_US'),

    'cipher' => 'AES-256-CBC',

    'key' => env('APP_KEY'),

    'previous_keys' => [
        ...array_filter(
            explode(',', (string) env('APP_PREVIOUS_KEYS', ''))
        ),
    ],

    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],

];
