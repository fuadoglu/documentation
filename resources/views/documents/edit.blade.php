<x-app-layout>
    <x-slot name="header">
        {{ __('ui.documents.edit_title') }}
    </x-slot>

    <section class="app-card">
        <form method="POST" action="{{ route('documents.update', $document) }}" class="grid gap-4 md:grid-cols-2">
            @csrf
            @method('PUT')

            <div class="md:col-span-2">
                <p class="app-label">{{ __('ui.documents.prefix') }}</p>
                <code class="mt-1 inline-block break-all rounded-md bg-slate-900 px-3 py-1.5 text-sm font-semibold text-teal-300">{{ $document->prefix_code }}</code>
            </div>

            <div class="md:col-span-2">
                <label for="title" class="app-label">{{ __('ui.documents.name') }}</label>
                <input id="title" name="title" value="{{ old('title', $document->title) }}" required class="app-input" maxlength="255">
            </div>

            <div>
                <label for="category_id" class="app-label">{{ __('ui.documents.category') }}</label>
                <select id="category_id" name="category_id" required class="app-input">
                    <option value="">{{ __('ui.common.select') }}</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('category_id', $document->category_id) == $category->id)>{{ $category->name }} ({{ $category->code }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="folder_id" class="app-label">{{ __('ui.documents.folder') }}</label>
                <select id="folder_id" name="folder_id" required class="app-input">
                    <option value="">{{ __('ui.common.select') }}</option>
                    @foreach ($folders as $folder)
                        <option value="{{ $folder->id }}" @selected(old('folder_id', $document->folder_id) == $folder->id)>
                            {{ $folder->parent?->name ? $folder->parent->name.' / ' : '' }}{{ $folder->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-2">
                <label for="description" class="app-label">{{ __('ui.documents.description_optional') }}</label>
                <textarea id="description" name="description" rows="4" class="app-input">{{ old('description', $document->description) }}</textarea>
            </div>

            <div class="app-action-group md:col-span-2">
                <button type="submit" class="app-button-primary w-full sm:w-auto"><x-icon name="edit" class="h-5 w-5" /><span>{{ __('ui.documents.update_submit') }}</span></button>
                <a href="{{ route('documents.show', $document) }}" class="app-button-secondary w-full sm:w-auto"><x-icon name="open" class="h-5 w-5" /><span>{{ __('ui.common.open') }}</span></a>
            </div>
        </form>
    </section>

    @include('documents.partials.version-manager')
</x-app-layout>
