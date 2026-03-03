<x-app-layout>
    <x-slot name="header">
        {{ __('ui.documents.detail_title') }}
    </x-slot>

    <section class="app-card space-y-4">
        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
            <p class="text-xs text-slate-500">{{ __('ui.documents.prefix') }}</p>
            <div class="mt-2 flex flex-col gap-2 sm:flex-row sm:items-center">
                <code id="prefix-code" class="max-w-full break-all rounded-md bg-slate-900 px-3 py-1.5 text-sm font-semibold text-teal-300">{{ $document->prefix_code }}</code>
                <button type="button" data-copy-target="#prefix-code" class="app-button-secondary w-full sm:w-auto"><x-icon name="copy" class="h-5 w-5" /><span>{{ __('ui.common.copy') }}</span></button>
            </div>
        </div>

        <div>
            <p class="text-xs text-slate-500">{{ __('ui.documents.name') }}</p>
            <p class="text-base font-semibold text-slate-900">{{ $document->title }}</p>
        </div>

        @if ($canManage)
            <div class="app-action-group">
                <a href="{{ route('documents.edit', $document) }}" class="app-button-secondary w-full sm:w-auto"><x-icon name="edit" class="h-5 w-5" /><span>{{ __('ui.documents.edit') }}</span></a>
                <form method="POST" action="{{ route('documents.destroy', $document) }}" data-confirm="{{ __('ui.documents.confirm_delete') }}">
                    @csrf
                    @method('DELETE')
                    <button class="app-button-danger w-full sm:w-auto"><x-icon name="delete" class="h-5 w-5" /><span>{{ __('ui.common.delete') }}</span></button>
                </form>
            </div>
        @endif

        <div class="grid gap-3 sm:grid-cols-2">
            <div>
                <p class="text-xs text-slate-500">{{ __('ui.documents.category') }}</p>
                <p class="text-sm font-medium text-slate-900">{{ $document->category->name ?? '-' }}</p>
            </div>

            <div>
                <p class="text-xs text-slate-500">{{ __('ui.documents.folder') }}</p>
                <p class="text-sm font-medium text-slate-900">{{ $document->folder->parent?->name ? $document->folder->parent->name.' / ' : '' }}{{ $document->folder->name ?? '-' }}</p>
            </div>

            <div>
                <p class="text-xs text-slate-500">{{ __('ui.documents.created_by') }}</p>
                <p class="text-sm font-medium text-slate-900">{{ $document->creator->name ?? '-' }}</p>
            </div>

            <div>
                <p class="text-xs text-slate-500">{{ __('ui.documents.created_at') }}</p>
                <p class="text-sm font-medium text-slate-900">{{ $document->created_at->format('d.m.Y H:i') }}</p>
            </div>
        </div>

        @if ($document->description)
            <div>
                <p class="text-xs text-slate-500">{{ __('ui.documents.description') }}</p>
                <p class="text-sm text-slate-800">{{ $document->description }}</p>
            </div>
        @endif
    </section>

    @include('documents.partials.version-manager')
</x-app-layout>
