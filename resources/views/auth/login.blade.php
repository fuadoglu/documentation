<x-guest-layout>
    @php
        $companyDisplayName = $branding?->company_name ?: (config('app.name') ?: __('ui.brand.platform'));
    @endphp

    <div class="mb-6">
        <p class="text-xs uppercase tracking-[0.16em] text-teal-300">{{ __('ui.brand.platform') }}</p>
        @if (! empty($branding?->logo_path))
            <img
                src="{{ route('branding.logo') }}"
                alt="{{ $companyDisplayName }}"
                class="mt-2 h-14 w-auto max-w-[220px] object-contain"
            >
        @else
            <h1 class="mt-2 text-2xl font-bold">{{ $companyDisplayName }}</h1>
        @endif
        <p class="mt-1 text-sm text-slate-400">{{ __('ui.login.subtitle') }}</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="mb-1 block text-sm font-medium text-slate-200">{{ __('ui.login.email') }}</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="block w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-slate-100 focus:border-teal-400 focus:outline-none focus:ring-0">
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <label for="password" class="mb-1 block text-sm font-medium text-slate-200">{{ __('ui.login.password') }}</label>
            <input id="password" name="password" type="password" required autocomplete="current-password" class="block w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-slate-100 focus:border-teal-400 focus:outline-none focus:ring-0">
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center gap-2 text-sm text-slate-400">
            <input id="remember" name="remember" type="checkbox" class="rounded border-slate-600 bg-slate-800 text-teal-500 focus:ring-teal-500">
            <label for="remember">{{ __('ui.login.remember') }}</label>
        </div>

        <button type="submit" class="app-button-primary w-full">
            {{ __('ui.login.submit') }}
        </button>
    </form>
</x-guest-layout>
