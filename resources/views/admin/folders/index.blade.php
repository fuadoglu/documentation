<x-app-layout>
    <x-slot name="header">
        {{ __('ui.admin.folders.title') }}
    </x-slot>

    <section class="app-card space-y-4">
        <h3 class="text-sm font-semibold text-slate-900">{{ __('ui.admin.folders.new_folder') }}</h3>

        @php
            $createFormScope = old('form_scope');
            $createValues = [];
            foreach ($locales as $locale) {
                $createValues[$locale] = $createFormScope === 'create-folder'
                    ? old("name_translations.$locale")
                    : '';
            }
        @endphp

        <form method="POST" action="{{ route('admin.folders.store') }}" class="grid items-end gap-3 md:grid-cols-2 xl:grid-cols-4">
            @csrf
            <input type="hidden" name="form_scope" value="create-folder">
            <input name="code" placeholder="{{ __('ui.admin.folders.code_placeholder') }}" class="app-input" required>

            <select name="parent_id" class="app-input">
                <option value="" @selected(old('parent_id') === null || old('parent_id') === '')>{{ __('ui.admin.folders.root_option') }}</option>
                @foreach ($parentFolders as $parent)
                    <option value="{{ $parent->id }}" @selected((string) old('parent_id') === (string) $parent->id)>
                        {{ $parent->name }}
                    </option>
                @endforeach
            </select>
            <input name="sort_order" type="number" min="0" placeholder="{{ __('ui.admin.folders.sort_placeholder') }}" class="app-input">
            <button type="submit" class="app-button-primary w-full">{{ __('ui.common.create') }}</button>
            <div class="md:col-span-2 xl:col-span-4">
                <x-translatable-name-tabs :locales="$locales" :values="$createValues" :max-length="150" />
                @error('name_translations')
                    <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            </div>
        </form>
    </section>

    <section class="space-y-3">
        @foreach ($folders as $folder)
            <article class="app-card space-y-3">
                @php
                    $formScope = 'folder-'.$folder->id;
                    $isCurrentFormOld = old('form_scope') === $formScope;
                    $updateValues = [];
                    foreach ($locales as $locale) {
                        $storedValue = $folder->name_translations[$locale] ?? null;
                        if ($storedValue === null && $locale === config('app.locale', 'az')) {
                            $storedValue = $folder->getRawOriginal('name');
                        }

                        $updateValues[$locale] = $isCurrentFormOld
                            ? old("name_translations.$locale")
                            : $storedValue;
                    }
                @endphp
                <form method="POST" action="{{ route('admin.folders.update', $folder) }}" class="grid items-end gap-3 md:grid-cols-2 xl:grid-cols-4">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="form_scope" value="{{ $formScope }}">
                    <input name="code" value="{{ $folder->code }}" class="app-input" required>

                    <select name="parent_id" class="app-input">
                        <option value="" @selected($folder->parent_id === null)>{{ __('ui.admin.folders.root_option') }}</option>
                        @foreach ($parentFolders as $parent)
                            @continue($parent->id === $folder->id)
                            <option value="{{ $parent->id }}" @selected($folder->parent_id == $parent->id)>
                                {{ $parent->name }}
                            </option>
                        @endforeach
                    </select>
                    <input name="sort_order" type="number" min="0" value="{{ $folder->sort_order }}" class="app-input">
                    <button class="app-button-secondary w-full">{{ __('ui.common.update') }}</button>
                    <div class="md:col-span-2 xl:col-span-4">
                        <x-translatable-name-tabs :locales="$locales" :values="$updateValues" :max-length="150" />
                        @if ($isCurrentFormOld)
                            @error('name_translations')
                                <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
                            @enderror
                        @endif
                    </div>
                </form>

                <p class="text-xs text-slate-500">
                    {{ __('ui.admin.folders.type') }}:
                    {{ $folder->parent_id ? __('ui.admin.folders.type_child') : __('ui.admin.folders.type_parent') }}
                    @if($folder->parent) ({{ $folder->parent->name }}) @endif
                </p>

                <div class="flex flex-wrap gap-2">
                    <form method="POST" action="{{ route('admin.folders.status', $folder) }}">
                        @csrf
                        @method('PATCH')
                        <button class="app-button-secondary">{{ $folder->is_active ? __('ui.common.deactivate') : __('ui.common.activate') }}</button>
                    </form>

                    <form method="POST" action="{{ route('admin.folders.destroy', $folder) }}" data-confirm="{{ __('ui.admin.folders.confirm_delete') }}">
                        @csrf
                        @method('DELETE')
                        <button class="app-button-danger">{{ __('ui.common.delete') }}</button>
                    </form>
                </div>
            </article>
        @endforeach
    </section>

    <div>{{ $folders->links() }}</div>
</x-app-layout>
