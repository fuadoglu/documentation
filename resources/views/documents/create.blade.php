<x-app-layout>
    <x-slot name="header">
        {{ __('ui.documents.create_title') }}
    </x-slot>

    <section class="app-card">
        <form method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data" class="grid gap-4 md:grid-cols-2" data-prefix-endpoint="{{ route('documents.prefix-preview') }}">
            @csrf

            <div class="md:col-span-2 rounded-xl border border-slate-200 bg-slate-50 p-3">
                <p class="text-xs text-slate-500">{{ __('ui.documents.prefix_preview') }}</p>
                <div class="mt-1 flex flex-col gap-2 sm:flex-row sm:items-center">
                    <code id="prefix-preview" data-loading-text="{{ __('ui.documents.prefix_loading') }}" class="inline-block break-all rounded-md bg-slate-900 px-3 py-1.5 text-sm font-semibold text-teal-300">-</code>
                    <button type="button" data-copy-target="#prefix-preview" class="app-button-secondary w-full sm:w-auto">
                        <x-icon name="copy" class="h-5 w-5" />
                        <span>{{ __('ui.common.copy') }}</span>
                    </button>
                </div>
            </div>

            <div class="md:col-span-2">
                <label for="title" class="app-label">{{ __('ui.documents.name') }}</label>
                <input id="title" name="title" value="{{ old('title') }}" required class="app-input" maxlength="255">
            </div>

            <div>
                <label for="category_id" class="app-label">{{ __('ui.documents.category') }}</label>
                <select id="category_id" name="category_id" required class="app-input">
                    <option value="">{{ __('ui.common.select') }}</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }} ({{ $category->code }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="folder_id" class="app-label">{{ __('ui.documents.folder') }}</label>
                <select id="folder_id" name="folder_id" required class="app-input">
                    <option value="">{{ __('ui.common.select') }}</option>
                    @foreach ($folders as $folder)
                        <option value="{{ $folder->id }}" @selected(old('folder_id') == $folder->id)>
                            {{ $folder->parent?->name ? $folder->parent->name.' / ' : '' }}{{ $folder->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-2">
                <label for="description" class="app-label">{{ __('ui.documents.description_optional') }}</label>
                <textarea id="description" name="description" rows="4" class="app-input">{{ old('description') }}</textarea>
            </div>

            @if ($attachmentsEnabled)
                <div class="md:col-span-2">
                    <label for="file" class="app-label">{{ __('ui.documents.file_optional') }}</label>
                    <div data-dropzone class="version-dropzone">
                        <input id="file" name="file" type="file" class="sr-only">
                        <div class="pointer-events-none flex w-full flex-col items-center justify-center gap-1 text-center">
                            <x-icon name="attachments" class="h-7 w-7 text-teal-700" />
                            <p data-dropzone-label class="text-sm font-semibold text-slate-900">{{ __('ui.documents.file_drop_title') }}</p>
                            <p class="text-xs text-slate-500">{{ __('ui.documents.file_drop_hint') }}</p>
                        </div>
                    </div>
                    @error('file')
                        <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            <div class="md:col-span-2">
                <button type="submit" class="app-button-primary w-full sm:w-auto">{{ __('ui.documents.create_submit') }}</button>
            </div>
        </form>
    </section>
</x-app-layout>
