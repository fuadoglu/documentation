<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @php
            $brandPrimary = $branding?->primary_color ?: '#0F766E';
            $companyDisplayName = $branding?->company_name ?: (config('app.name') ?: __('ui.brand.platform'));
            $themeCssVersion = $branding?->updated_at?->timestamp ?? 1;
        @endphp
        <meta name="theme-color" content="{{ $brandPrimary }}">
        <link rel="manifest" href="/manifest.webmanifest">
        @if (! empty($branding?->favicon_path))
            <link rel="icon" href="{{ route('branding.favicon') }}">
            <link rel="shortcut icon" href="{{ route('branding.favicon') }}">
        @endif

        <title>{{ $companyDisplayName }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="{{ route('branding.theme', ['v' => $themeCssVersion]) }}">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen overflow-x-hidden bg-slate-950 text-slate-100">
        <div class="app-shell-mobile app-guest-safe relative flex min-h-screen items-center justify-center overflow-hidden px-4 py-8 sm:py-12">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(13,148,136,0.3),transparent_45%),radial-gradient(circle_at_80%_0%,rgba(59,130,246,0.22),transparent_50%)]"></div>

            <div class="absolute right-3 top-3 z-20 sm:right-4 sm:top-4">
                <x-locale-dropdown transparent />
            </div>

            <div class="app-glass relative z-10 w-full max-w-md rounded-3xl border border-slate-800 bg-slate-900/90 p-5 shadow-2xl sm:p-6">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
