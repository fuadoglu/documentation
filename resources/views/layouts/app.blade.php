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
    <body class="min-h-screen overflow-x-hidden bg-slate-100 text-slate-900">
        @php
            $user = auth()->user();
            $isAdmin = $user?->hasRole('admin');
        @endphp

        <div class="min-h-screen w-full bg-white md:grid md:grid-cols-[260px,1fr]">
            <aside class="hidden border-r border-slate-200 bg-slate-950 text-slate-100 md:sticky md:top-0 md:flex md:h-screen md:flex-col md:overflow-hidden">
                <div class="border-b border-slate-800 p-5">
                    @if (! empty($branding?->logo_path))
                        <img
                            src="{{ route('branding.logo') }}"
                            alt="{{ $companyDisplayName }}"
                            class="h-12 w-auto max-w-[180px] object-contain"
                        >
                    @else
                        <p class="text-xs uppercase tracking-[0.16em] text-teal-300">{{ __('ui.brand.platform') }}</p>
                        <h1 class="mt-2 text-lg font-bold">{{ $companyDisplayName }}</h1>
                    @endif
                </div>

                <nav class="flex-1 space-y-1 overflow-y-auto p-3 text-sm">
                    <a href="{{ route('dashboard') }}" class="app-nav {{ request()->routeIs('dashboard') ? 'app-nav-active' : '' }}"><x-icon name="home-main" class="h-5 w-5 shrink-0" /><span>{{ __('ui.nav.dashboard') }}</span></a>
                    @can('documents.view')
                        <a href="{{ route('documents.index') }}" class="app-nav {{ request()->routeIs('documents.index') ? 'app-nav-active' : '' }}"><x-icon name="documents" class="h-5 w-5 shrink-0" /><span>{{ __('ui.nav.documents') }}</span></a>
                    @endcan
                    @can('documents.create')
                        <a href="{{ route('documents.create') }}" class="app-nav {{ request()->routeIs('documents.create') ? 'app-nav-active' : '' }}"><x-icon name="create" class="h-5 w-5 shrink-0" /><span>{{ __('ui.nav.create_document') }}</span></a>
                    @endcan
                    <a href="{{ route('profile.edit') }}" class="app-nav {{ request()->routeIs('profile.edit') ? 'app-nav-active' : '' }}"><x-icon name="profile" class="h-5 w-5 shrink-0" /><span>{{ __('ui.nav.profile') }}</span></a>

                    @if ($isAdmin)
                        <div class="my-4 border-t border-slate-800"></div>
                        <p class="flex items-center gap-2 px-3 py-2 text-xs uppercase tracking-[0.12em] text-slate-400"><x-icon name="admin" class="h-5 w-5" />{{ __('ui.nav.admin') }}</p>
                        <a href="{{ route('admin.users.index') }}" class="app-nav {{ request()->routeIs('admin.users.*') ? 'app-nav-active' : '' }}"><x-icon name="users" class="h-5 w-5 shrink-0" /><span>{{ __('ui.nav.users') }}</span></a>
                        <a href="{{ route('admin.permissions.index') }}" class="app-nav {{ request()->routeIs('admin.permissions.*') ? 'app-nav-active' : '' }}"><x-icon name="permissions" class="h-5 w-5 shrink-0" /><span>{{ __('ui.nav.permissions') }}</span></a>
                        <a href="{{ route('admin.categories.index') }}" class="app-nav {{ request()->routeIs('admin.categories.*') ? 'app-nav-active' : '' }}"><x-icon name="categories" class="h-5 w-5 shrink-0" /><span>{{ __('ui.nav.categories') }}</span></a>
                        <a href="{{ route('admin.folders.index') }}" class="app-nav {{ request()->routeIs('admin.folders.*') ? 'app-nav-active' : '' }}"><x-icon name="folders" class="h-5 w-5 shrink-0" /><span>{{ __('ui.nav.folders') }}</span></a>
                        <a href="{{ route('admin.branding.edit') }}" class="app-nav {{ request()->routeIs('admin.branding.*') ? 'app-nav-active' : '' }}"><x-icon name="branding" class="h-5 w-5 shrink-0" /><span>{{ __('ui.nav.branding') }}</span></a>
                        <a href="{{ route('admin.audit-logs.index') }}" class="app-nav {{ request()->routeIs('admin.audit-logs.*') ? 'app-nav-active' : '' }}"><x-icon name="audit" class="h-5 w-5 shrink-0" /><span>{{ __('ui.nav.audit_logs') }}</span></a>
                    @endif
                </nav>

                <div class="border-t border-slate-800 p-3">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="app-button-dark w-full">
                            <x-icon name="logout" class="h-5 w-5" />
                            <span>{{ __('ui.nav.logout') }}</span>
                        </button>
                    </form>
                </div>
            </aside>

            <div class="app-shell-mobile flex min-h-screen flex-col">
                <header class="app-glass app-safe-top sticky top-0 z-20 border-b border-slate-200 bg-white/95 px-4 py-3 md:px-6">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div class="min-w-0 flex-1">
                            @isset($header)
                                <h2 class="truncate text-lg font-semibold text-slate-900">{{ $header }}</h2>
                            @else
                                @if (! empty($branding?->logo_path))
                                    <img
                                        src="{{ route('branding.logo') }}"
                                        alt="{{ $companyDisplayName }}"
                                        class="h-8 w-auto max-w-[160px] object-contain"
                                    >
                                @else
                                    <h2 class="truncate text-lg font-semibold text-slate-900">{{ $companyDisplayName }}</h2>
                                @endif
                            @endisset
                            <p class="truncate text-xs text-slate-500">{{ auth()->user()->name }}</p>
                        </div>

                        <div class="ml-auto flex flex-wrap items-center justify-end gap-1">
                            <x-locale-dropdown />
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="app-button-secondary md:hidden"><x-icon name="logout" class="h-5 w-5" /><span>{{ __('ui.nav.logout') }}</span></button>
                            </form>
                        </div>
                    </div>
                </header>

                <main class="app-main-mobile-safe flex-1 space-y-4 px-4 pt-4 md:px-6">
                    @if (session('status'))
                        <div class="rounded-xl border border-teal-200 bg-teal-50 px-4 py-3 text-sm text-teal-800">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                            <ul class="space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{ $slot }}
                </main>

                <nav class="app-safe-bottom fixed bottom-0 left-0 right-0 z-30 border-t border-slate-200 bg-white pt-2 md:hidden">
                    <div class="grid w-full grid-cols-4 gap-2 text-center text-xs">
                        <a href="{{ route('dashboard') }}" class="tab-link {{ request()->routeIs('dashboard') ? 'tab-link-active' : '' }}"><x-icon name="home-main" class="h-6 w-6" /><span>{{ __('ui.nav.dashboard') }}</span></a>
                        @can('documents.view')
                            <a href="{{ route('documents.index') }}" class="tab-link {{ request()->routeIs('documents.index') ? 'tab-link-active' : '' }}"><x-icon name="documents" class="h-6 w-6" /><span>{{ __('ui.nav.documents') }}</span></a>
                        @else
                            <span class="tab-link opacity-40"><x-icon name="documents" class="h-6 w-6" /><span>{{ __('ui.nav.documents') }}</span></span>
                        @endcan
                        @can('documents.create')
                            <a href="{{ route('documents.create') }}" class="tab-link {{ request()->routeIs('documents.create') ? 'tab-link-active' : '' }}"><x-icon name="create" class="h-6 w-6" /><span>{{ __('ui.nav.create_document') }}</span></a>
                        @else
                            <span class="tab-link opacity-40"><x-icon name="create" class="h-6 w-6" /><span>{{ __('ui.nav.create_document') }}</span></span>
                        @endcan
                        <a href="{{ route('profile.edit') }}" class="tab-link {{ request()->routeIs('profile.edit') ? 'tab-link-active' : '' }}"><x-icon name="profile" class="h-6 w-6" /><span>{{ __('ui.nav.profile') }}</span></a>
                    </div>
                </nav>
            </div>
        </div>
    </body>
</html>
