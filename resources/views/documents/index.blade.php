<x-app-layout>
    <x-slot name="header">
        {{ __('ui.documents.title') }}
    </x-slot>

    @php
        $hasActiveFilters = collect($filters ?? [])->contains(fn ($value) => filled($value));
    @endphp

    <section class="app-card" x-data="{ filtersOpen: {{ $hasActiveFilters ? 'true' : 'false' }} }">
        <div class="mb-3 md:hidden">
            <button
                type="button"
                class="app-button-secondary w-full justify-between"
                @click="filtersOpen = !filtersOpen"
                :aria-expanded="filtersOpen.toString()"
                aria-controls="documents-filters"
            >
                <span x-show="!filtersOpen">{{ __('ui.common.show_filters') }}</span>
                <span x-show="filtersOpen" x-cloak>{{ __('ui.common.hide_filters') }}</span>
                <x-icon name="chevron-down" class="h-5 w-5 transition" x-bind:class="filtersOpen ? 'rotate-180' : ''" />
            </button>
        </div>

        <form id="documents-filters" method="GET" action="{{ route('documents.index') }}" class="hidden gap-3 md:grid sm:grid-cols-2 xl:grid-cols-3" :class="{ '!grid': filtersOpen }">
            <div class="sm:col-span-2 lg:col-span-1">
                <label class="app-label" for="q">{{ __('ui.documents.name') }}</label>
                <input id="q" name="q" value="{{ $filters['q'] ?? '' }}" class="app-input" placeholder="{{ __('ui.documents.search_placeholder') }}">
            </div>

            @if ($canFilterUsers)
                <div>
                    <label class="app-label" for="created_by">{{ __('ui.documents.user') }}</label>
                    <select id="created_by" name="created_by" class="app-input">
                        <option value="">{{ __('ui.common.all') }}</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" @selected(($filters['created_by'] ?? '') == $user->id)>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div>
                <label class="app-label" for="category_id">{{ __('ui.documents.category') }}</label>
                <select id="category_id" name="category_id" class="app-input">
                    <option value="">{{ __('ui.common.all') }}</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(($filters['category_id'] ?? '') == $category->id)>{{ $category->name }} ({{ $category->code }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="app-label" for="folder_id">{{ __('ui.documents.folder') }}</label>
                <select id="folder_id" name="folder_id" class="app-input">
                    <option value="">{{ __('ui.common.all') }}</option>
                    @foreach ($folders as $folder)
                        <option value="{{ $folder->id }}" @selected(($filters['folder_id'] ?? '') == $folder->id)>
                            {{ $folder->parent ? $folder->parent->name.' / ' : '' }}{{ $folder->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="app-label" for="date_from">{{ __('ui.documents.from_date') }}</label>
                <input id="date_from" type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="app-input">
            </div>

            <div>
                <label class="app-label" for="date_to">{{ __('ui.documents.to_date') }}</label>
                <input id="date_to" type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="app-input">
            </div>

            <div class="app-action-group sm:col-span-2 xl:col-span-3">
                <button type="submit" class="app-button-primary w-full sm:w-auto">{{ __('ui.common.filter') }}</button>
                <a href="{{ route('documents.index') }}" class="app-button-secondary w-full sm:w-auto">{{ __('ui.common.clear') }}</a>
            </div>
        </form>
    </section>

    <section class="space-y-3">
        @forelse ($documents as $document)
            <article class="app-card">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <p class="break-all text-xs font-semibold uppercase tracking-[0.1em] text-teal-700">{{ $document->prefix_code }}</p>
                        <h3 class="mt-1 text-base font-semibold text-slate-900">{{ $document->title }}</h3>
                        <p class="mt-1 text-xs text-slate-500 sm:text-sm">
                            {{ $document->category->name ?? '-' }} •
                            {{ $document->folder->name ?? '-' }} •
                            {{ $document->creator->name ?? '-' }} •
                            {{ $document->created_at->format('d.m.Y H:i') }}
                        </p>
                    </div>

                    @php
                        $canManageDocument = auth()->user()->hasRole('admin') || $document->created_by === auth()->id();
                    @endphp
                    <div class="{{ $canManageDocument ? 'doc-actions-grid' : 'w-full sm:w-auto' }}">
                        <a href="{{ route('documents.show', $document) }}" class="app-button-secondary doc-action-btn min-w-0"><x-icon name="open" /><span>{{ __('ui.common.open') }}</span></a>
                        @if ($canManageDocument)
                            <a href="{{ route('documents.edit', $document) }}" class="app-button-secondary doc-action-btn min-w-0"><x-icon name="edit" /><span>{{ __('ui.documents.edit') }}</span></a>
                            <form method="POST" action="{{ route('documents.destroy', $document) }}" data-confirm="{{ __('ui.documents.confirm_delete') }}" class="min-w-0 w-full sm:w-auto">
                                @csrf
                                @method('DELETE')
                                <button class="app-button-danger doc-action-btn min-w-0"><x-icon name="delete" /><span>{{ __('ui.common.delete') }}</span></button>
                            </form>
                        @endif
                    </div>
                </div>
            </article>
        @empty
            <div class="app-card text-sm text-slate-500">{{ __('ui.documents.not_found') }}</div>
        @endforelse
    </section>

    @if ($documents->count() > 0)
        <div class="pb-2">
            <div>
                {{ $documents->onEachSide(1)->links() }}
            </div>
        </div>
    @endif
</x-app-layout>
