<x-app-layout>
    <x-slot name="header">
        {{ __('ui.admin.users.title') }}
    </x-slot>

    <section class="app-card space-y-4">
        <h3 class="text-sm font-semibold text-slate-900">{{ __('ui.admin.users.new_user') }}</h3>

        <form method="POST" action="{{ route('admin.users.store') }}" class="grid gap-3 md:grid-cols-2 xl:grid-cols-6">
            @csrf
            @php
                $createFormScope = old('form_scope');
            @endphp
            <input type="hidden" name="form_scope" value="create-user">
            <input name="name" placeholder="{{ __('ui.admin.users.full_name') }}" class="app-input" required>
            <input name="email" type="email" placeholder="{{ __('ui.profile.email') }}" class="app-input" required>
            <input name="password" type="password" placeholder="{{ __('ui.admin.users.password_required') }}" class="app-input" required autocomplete="new-password">
            <input name="password_confirmation" type="password" placeholder="{{ __('ui.admin.users.password_confirm') }}" class="app-input" required autocomplete="new-password">
            <select name="locale" class="app-input" required>
                @foreach ($locales as $locale)
                    @php
                        $localeLabelKey = 'ui.common.language_'.$locale;
                        $localeLabel = __($localeLabelKey);
                        if ($localeLabel === $localeLabelKey) {
                            $localeLabel = strtoupper($locale);
                        }
                    @endphp
                    <option value="{{ $locale }}" @selected(($createFormScope === 'create-user' ? old('locale') : config('app.locale', 'az')) === $locale)>{{ $localeLabel }}</option>
                @endforeach
            </select>

            <select name="role" class="app-input" required>
                <option value="employee">{{ __('ui.admin.users.role_employee') }}</option>
                <option value="admin">{{ __('ui.admin.users.role_admin') }}</option>
            </select>

            <label class="flex items-center gap-2 rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" checked>
                {{ __('ui.common.active') }}
            </label>

            <button type="submit" class="app-button-primary w-full md:col-span-2 xl:col-span-1">{{ __('ui.common.create') }}</button>
        </form>
    </section>

    <section class="space-y-3">
        @foreach ($users as $item)
            <article class="app-card space-y-3">
                <form method="POST" action="{{ route('admin.users.update', $item) }}" class="grid gap-3 md:grid-cols-2 xl:grid-cols-6">
                    @csrf
                    @method('PUT')
                    @php
                        $editFormScope = old('form_scope');
                        $formScope = 'user-'.$item->id;
                        $isCurrentFormOld = $editFormScope === $formScope;
                    @endphp
                    <input type="hidden" name="form_scope" value="{{ $formScope }}">
                    <input name="name" value="{{ $item->name }}" class="app-input" required>
                    <input name="email" type="email" value="{{ $item->email }}" class="app-input" required>
                    <input name="password" type="password" placeholder="{{ __('ui.admin.users.new_password_optional') }}" class="app-input" autocomplete="new-password">
                    <input name="password_confirmation" type="password" placeholder="{{ __('ui.admin.users.new_password_confirm_optional') }}" class="app-input" autocomplete="new-password">
                    <select name="locale" class="app-input" required>
                        @foreach ($locales as $locale)
                            @php
                                $localeLabelKey = 'ui.common.language_'.$locale;
                                $localeLabel = __($localeLabelKey);
                                if ($localeLabel === $localeLabelKey) {
                                    $localeLabel = strtoupper($locale);
                                }
                            @endphp
                            <option value="{{ $locale }}" @selected(($isCurrentFormOld ? old('locale') : $item->locale) === $locale)>{{ $localeLabel }}</option>
                        @endforeach
                    </select>

                    <select name="role" class="app-input" required>
                        <option value="employee" @selected($item->hasRole('employee'))>{{ __('ui.admin.users.role_employee') }}</option>
                        <option value="admin" @selected($item->hasRole('admin'))>{{ __('ui.admin.users.role_admin') }}</option>
                    </select>

                    <label class="flex items-center gap-2 rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" @checked($item->is_active)>
                        {{ __('ui.common.active') }}
                    </label>

                    <button class="app-button-secondary w-full md:col-span-2 xl:col-span-1">{{ __('ui.common.update') }}</button>
                </form>

                <div class="flex flex-wrap gap-2">
                    <form method="POST" action="{{ route('admin.users.status', $item) }}">
                        @csrf
                        @method('PATCH')
                        <button class="app-button-secondary">{{ $item->is_active ? __('ui.common.deactivate') : __('ui.common.activate') }}</button>
                    </form>

                    <details class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                        <summary class="cursor-pointer text-xs font-semibold text-slate-700">{{ __('ui.admin.users.reset_password') }}</summary>
                        <form method="POST" action="{{ route('admin.users.reset-password', $item) }}" class="mt-2 flex flex-wrap items-center gap-2">
                            @csrf
                            <input name="password" type="password" required autocomplete="new-password" class="app-input min-w-[180px] flex-1" placeholder="{{ __('ui.admin.users.reset_password_new') }}">
                            <input name="password_confirmation" type="password" required autocomplete="new-password" class="app-input min-w-[180px] flex-1" placeholder="{{ __('ui.admin.users.reset_password_confirm') }}">
                            <button class="app-button-secondary">{{ __('ui.admin.users.reset_password_submit') }}</button>
                        </form>
                    </details>

                    <form method="POST" action="{{ route('admin.users.destroy', $item) }}" data-confirm="{{ __('ui.admin.users.confirm_delete') }}">
                        @csrf
                        @method('DELETE')
                        <button class="app-button-danger">{{ __('ui.common.delete') }}</button>
                    </form>
                </div>
            </article>
        @endforeach
    </section>

    <div>{{ $users->links() }}</div>
</x-app-layout>
