<x-app-layout>
    <x-slot name="header">
        {{ __('ui.admin.branding.title') }}
    </x-slot>

    <section class="app-card">
        <form method="POST" action="{{ route('admin.branding.update') }}" enctype="multipart/form-data" class="grid gap-4 md:grid-cols-2">
            @csrf
            @method('PUT')

            <div class="md:col-span-2">
                <label class="app-label" for="company_name">{{ __('ui.admin.branding.company_name') }}</label>
                <input id="company_name" name="company_name" value="{{ old('company_name', $settings->company_name) }}" class="app-input" required>
            </div>

            <div class="md:col-span-2">
                <label class="app-label" for="allowed_login_domain">{{ __('ui.admin.branding.login_domain') }}</label>
                <input id="allowed_login_domain" name="allowed_login_domain" value="{{ old('allowed_login_domain', $settings->allowed_login_domain) }}" class="app-input" required placeholder="{{ __('ui.admin.branding.login_domain_placeholder') }}">
            </div>

            <div>
                <label class="app-label" for="attachments_enabled">{{ __('ui.admin.branding.attachments_toggle') }}</label>
                <select id="attachments_enabled" name="attachments_enabled" class="app-input">
                    <option value="1" @selected(old('attachments_enabled', (string) (int) $settings->attachments_enabled) === '1')>{{ __('ui.admin.branding.attachments_on') }}</option>
                    <option value="0" @selected(old('attachments_enabled', (string) (int) $settings->attachments_enabled) === '0')>{{ __('ui.admin.branding.attachments_off') }}</option>
                </select>
                <p class="mt-1 text-xs text-slate-500">{{ __('ui.admin.branding.attachments_hint') }}</p>
            </div>

            <div>
                <label class="app-label" for="primary_color">{{ __('ui.admin.branding.primary_color') }}</label>
                <input id="primary_color" name="primary_color" value="{{ old('primary_color', $settings->primary_color) }}" class="app-input" placeholder="#0F766E">
            </div>
            <div>
                <label class="app-label" for="secondary_color">{{ __('ui.admin.branding.secondary_color') }}</label>
                <input id="secondary_color" name="secondary_color" value="{{ old('secondary_color', $settings->secondary_color) }}" class="app-input" placeholder="#0B132B">
            </div>

            <div class="md:col-span-2">
                <label class="app-label" for="timezone">{{ __('ui.admin.branding.timezone') }}</label>
                <select id="timezone" name="timezone" class="app-input">
                    @php
                        $selectedTimezone = old('timezone', $settings->timezone ?? config('app.timezone', 'UTC'));
                    @endphp
                    <option value="Asia/Baku" @selected($selectedTimezone === 'Asia/Baku')>{{ __('ui.admin.branding.timezone_baku') }}</option>
                    @foreach ($timezones as $timezone)
                        @continue($timezone === 'Asia/Baku')
                        <option value="{{ $timezone }}" @selected($selectedTimezone === $timezone)>{{ $timezone }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-slate-500">{{ __('ui.admin.branding.timezone_hint') }}</p>
            </div>

            <div class="md:col-span-2">
                <label class="app-label" for="logo">{{ __('ui.admin.branding.logo') }}</label>
                <input id="logo" name="logo" type="file" accept=".jpg,.jpeg,.png,.webp,.svg,image/svg+xml" class="app-input">
                @if ($settings->logo_path)
                    <img src="{{ route('branding.logo') }}" alt="{{ __('ui.admin.branding.logo_alt') }}" class="mt-3 h-16 w-auto object-contain">
                @endif
            </div>

            <div class="md:col-span-2">
                <label class="app-label" for="favicon">{{ __('ui.admin.branding.favicon') }}</label>
                <input id="favicon" name="favicon" type="file" accept=".ico,.png,.svg,image/svg+xml" class="app-input">
                @if ($settings->favicon_path)
                    <img src="{{ route('branding.favicon') }}" alt="{{ __('ui.admin.branding.favicon_alt') }}" class="mt-3 h-10 w-10 rounded-md border border-slate-200 bg-white object-contain p-1">
                @endif
            </div>

            <div class="md:col-span-2">
                <button type="submit" class="app-button-primary w-full sm:w-auto">{{ __('ui.common.save') }}</button>
            </div>
        </form>
    </section>
</x-app-layout>
