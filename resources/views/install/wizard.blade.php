<x-guest-layout>
    <div class="space-y-6">
        <div class="text-center">
            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-brand">{{ __('ui.setup.badge') }}</p>
            <h1 class="mt-2 text-2xl font-extrabold text-white">{{ __('ui.setup.title') }}</h1>
            <p class="mt-2 text-sm text-slate-300">{{ __('ui.setup.subtitle') }}</p>
        </div>

        @if ($errors->has('setup'))
            <div class="rounded-2xl border border-red-500/40 bg-red-500/10 px-4 py-3 text-sm text-red-100">
                {{ $errors->first('setup') }}
            </div>
        @endif

        <div class="space-y-3 rounded-2xl border border-slate-800/80 bg-slate-900/70 p-4">
            <h2 class="text-sm font-semibold uppercase tracking-[0.14em] text-slate-300">{{ __('ui.setup.requirements_title') }}</h2>

            @foreach ($requirements as $group => $checks)
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">{{ __("ui.setup.requirement_group_{$group}") }}</p>
                <ul class="space-y-2">
                    @foreach ($checks as $check)
                        <li class="flex items-center justify-between gap-3 rounded-xl border border-slate-800 bg-slate-900 px-3 py-2 text-xs text-slate-200">
                            <span>{{ $check['label'] }}</span>
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $check['ok'] ? 'bg-emerald-500/15 text-emerald-300 ring-1 ring-emerald-500/30' : 'bg-rose-500/15 text-rose-300 ring-1 ring-rose-500/30' }}">
                                {{ $check['ok'] ? __('ui.setup.requirement_ok') : __('ui.setup.requirement_fail') }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endforeach

            @unless ($allRequirementsMet)
                <p class="rounded-xl border border-amber-500/30 bg-amber-500/10 px-3 py-2 text-xs text-amber-200">
                    {{ __('ui.setup.requirements_not_met') }}
                </p>
            @endunless
        </div>

        <form method="POST" action="{{ route('install.store') }}" class="space-y-5">
            @csrf

            <div class="space-y-3 rounded-2xl border border-slate-800/80 bg-slate-900/70 p-4">
                <h2 class="text-sm font-semibold uppercase tracking-[0.14em] text-slate-300">{{ __('ui.setup.section_application') }}</h2>
                <div class="grid gap-3">
                    <div>
                        <x-input-label for="company_name" :value="__('ui.setup.company_name')" />
                        <x-text-input id="company_name" name="company_name" type="text" class="mt-1 block w-full" :value="old('company_name', $defaults['company_name'])" required />
                        <x-input-error class="mt-1" :messages="$errors->get('company_name')" />
                    </div>
                    <div>
                        <x-input-label for="app_name" :value="__('ui.setup.app_name')" />
                        <x-text-input id="app_name" name="app_name" type="text" class="mt-1 block w-full" :value="old('app_name', $defaults['app_name'])" required />
                        <x-input-error class="mt-1" :messages="$errors->get('app_name')" />
                    </div>
                    <div>
                        <x-input-label for="app_url" :value="__('ui.setup.app_url')" />
                        <x-text-input id="app_url" name="app_url" type="url" class="mt-1 block w-full" :value="old('app_url', $defaults['app_url'])" required />
                        <x-input-error class="mt-1" :messages="$errors->get('app_url')" />
                    </div>
                    <div>
                        <x-input-label for="allowed_login_domain" :value="__('ui.setup.allowed_login_domain')" />
                        <x-text-input id="allowed_login_domain" name="allowed_login_domain" type="text" class="mt-1 block w-full" :value="old('allowed_login_domain', $defaults['allowed_login_domain'])" required />
                        <x-input-error class="mt-1" :messages="$errors->get('allowed_login_domain')" />
                    </div>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <x-input-label for="app_locale" :value="__('ui.setup.app_locale')" />
                            <select id="app_locale" name="app_locale" class="mt-1 block w-full rounded-xl border-slate-700 bg-slate-900 text-sm text-slate-100 focus:border-brand focus:ring-brand/40">
                                @foreach (config('app.available_locales', ['az', 'en']) as $locale)
                                    <option value="{{ $locale }}" @selected(old('app_locale', $defaults['app_locale']) === $locale)>
                                        {{ __('ui.common.language_'.$locale) }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-1" :messages="$errors->get('app_locale')" />
                        </div>
                        <div>
                            <x-input-label for="app_timezone" :value="__('ui.setup.app_timezone')" />
                            <select id="app_timezone" name="app_timezone" class="mt-1 block w-full rounded-xl border-slate-700 bg-slate-900 text-sm text-slate-100 focus:border-brand focus:ring-brand/40">
                                @foreach (timezone_identifiers_list() as $timezone)
                                    <option value="{{ $timezone }}" @selected(old('app_timezone', $defaults['app_timezone']) === $timezone)>{{ $timezone }}</option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-1" :messages="$errors->get('app_timezone')" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-3 rounded-2xl border border-slate-800/80 bg-slate-900/70 p-4">
                <h2 class="text-sm font-semibold uppercase tracking-[0.14em] text-slate-300">{{ __('ui.setup.section_database') }}</h2>
                <div class="grid gap-3">
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <x-input-label for="db_host" :value="__('ui.setup.db_host')" />
                            <x-text-input id="db_host" name="db_host" type="text" class="mt-1 block w-full" :value="old('db_host', $defaults['db_host'])" required />
                            <x-input-error class="mt-1" :messages="$errors->get('db_host')" />
                        </div>
                        <div>
                            <x-input-label for="db_port" :value="__('ui.setup.db_port')" />
                            <x-text-input id="db_port" name="db_port" type="number" min="1" max="65535" class="mt-1 block w-full" :value="old('db_port', $defaults['db_port'])" required />
                            <x-input-error class="mt-1" :messages="$errors->get('db_port')" />
                        </div>
                    </div>
                    <div>
                        <x-input-label for="db_database" :value="__('ui.setup.db_database')" />
                        <x-text-input id="db_database" name="db_database" type="text" class="mt-1 block w-full" :value="old('db_database', $defaults['db_database'])" required />
                        <x-input-error class="mt-1" :messages="$errors->get('db_database')" />
                    </div>
                    <div>
                        <x-input-label for="db_username" :value="__('ui.setup.db_username')" />
                        <x-text-input id="db_username" name="db_username" type="text" class="mt-1 block w-full" :value="old('db_username', $defaults['db_username'])" required />
                        <x-input-error class="mt-1" :messages="$errors->get('db_username')" />
                    </div>
                    <div>
                        <x-input-label for="db_password" :value="__('ui.setup.db_password')" />
                        <x-text-input id="db_password" name="db_password" type="password" class="mt-1 block w-full" :value="old('db_password', $defaults['db_password'])" />
                        <x-input-error class="mt-1" :messages="$errors->get('db_password')" />
                    </div>
                </div>
            </div>

            <div class="space-y-3 rounded-2xl border border-slate-800/80 bg-slate-900/70 p-4">
                <h2 class="text-sm font-semibold uppercase tracking-[0.14em] text-slate-300">{{ __('ui.setup.section_admin') }}</h2>
                <div class="grid gap-3">
                    <div>
                        <x-input-label for="admin_name" :value="__('ui.setup.admin_name')" />
                        <x-text-input id="admin_name" name="admin_name" type="text" class="mt-1 block w-full" :value="old('admin_name', $defaults['admin_name'])" required />
                        <x-input-error class="mt-1" :messages="$errors->get('admin_name')" />
                    </div>
                    <div>
                        <x-input-label for="admin_email" :value="__('ui.setup.admin_email')" />
                        <x-text-input id="admin_email" name="admin_email" type="email" class="mt-1 block w-full" :value="old('admin_email', $defaults['admin_email'])" required />
                        <x-input-error class="mt-1" :messages="$errors->get('admin_email')" />
                    </div>
                    <div>
                        <x-input-label for="admin_password" :value="__('ui.setup.admin_password')" />
                        <x-text-input id="admin_password" name="admin_password" type="password" class="mt-1 block w-full" required />
                        <x-input-error class="mt-1" :messages="$errors->get('admin_password')" />
                    </div>
                    <div>
                        <x-input-label for="admin_password_confirmation" :value="__('ui.setup.admin_password_confirmation')" />
                        <x-text-input id="admin_password_confirmation" name="admin_password_confirmation" type="password" class="mt-1 block w-full" required />
                    </div>
                </div>
            </div>

            <x-primary-button class="w-full justify-center" :disabled="! $allRequirementsMet">
                {{ __('ui.setup.submit') }}
            </x-primary-button>
        </form>
    </div>
</x-guest-layout>

