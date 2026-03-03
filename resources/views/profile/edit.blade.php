<x-app-layout>
    <x-slot name="header">
        {{ __('ui.profile.title') }}
    </x-slot>

    <section class="app-card space-y-4">
        <h3 class="text-sm font-semibold text-slate-900">{{ __('ui.profile.profile_info') }}</h3>

        <form method="POST" action="{{ route('profile.update') }}" class="grid gap-3 md:grid-cols-2">
            @csrf
            @method('PATCH')

            <div class="md:col-span-2">
                <label for="name" class="app-label">{{ __('ui.profile.full_name') }}</label>
                <input id="name" name="name" value="{{ old('name', $user->name) }}" class="app-input" required>
            </div>

            <div>
                <label for="email" class="app-label">{{ __('ui.profile.email') }}</label>
                <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" class="app-input" required>
            </div>

            <div>
                <label for="locale" class="app-label">{{ __('ui.common.language') }}</label>
                @php
                    $currentLocale = old('locale', $user->locale ?? app()->getLocale());
                    $locales = config('app.available_locales', ['az', 'en']);
                @endphp
                <select id="locale" name="locale" class="app-input" required>
                    @foreach ($locales as $locale)
                        @php
                            $localeLabelKey = 'ui.common.language_'.$locale;
                            $localeLabel = __($localeLabelKey);
                            if ($localeLabel === $localeLabelKey) {
                                $localeLabel = strtoupper($locale);
                            }
                        @endphp
                        <option value="{{ $locale }}" @selected($currentLocale === $locale)>{{ $localeLabel }}</option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-2">
                <button type="submit" class="app-button-primary w-full sm:w-auto">{{ __('ui.profile.update_profile') }}</button>
            </div>
        </form>
    </section>

    <section class="app-card space-y-4">
        <h3 class="text-sm font-semibold text-slate-900">{{ __('ui.profile.change_password') }}</h3>

        <form method="POST" action="{{ route('password.update') }}" class="grid gap-3 md:grid-cols-2">
            @csrf
            @method('PUT')

            <div class="md:col-span-2">
                <label for="current_password" class="app-label">{{ __('ui.profile.current_password') }}</label>
                <input id="current_password" name="current_password" type="password" class="app-input" required>
            </div>

            <div>
                <label for="password" class="app-label">{{ __('ui.profile.new_password') }}</label>
                <input id="password" name="password" type="password" class="app-input" required>
            </div>

            <div>
                <label for="password_confirmation" class="app-label">{{ __('ui.profile.new_password_confirm') }}</label>
                <input id="password_confirmation" name="password_confirmation" type="password" class="app-input" required>
            </div>

            <div class="md:col-span-2">
                <button type="submit" class="app-button-primary w-full sm:w-auto">{{ __('ui.profile.update_password') }}</button>
            </div>
        </form>
    </section>
</x-app-layout>
