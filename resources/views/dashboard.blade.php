<x-app-layout>
    <x-slot name="header">
        {{ __('ui.dashboard.title') }}
    </x-slot>

    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <article class="app-card">
            <p class="flex items-center gap-2 text-xs uppercase tracking-[0.12em] text-slate-500"><x-icon name="files-stack" class="h-5 w-5" />{{ __('ui.dashboard.total_documents') }}</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">{{ $totalDocuments }}</p>
        </article>

        <article class="app-card">
            <p class="flex items-center gap-2 text-xs uppercase tracking-[0.12em] text-slate-500"><x-icon name="today" class="h-5 w-5" />{{ __('ui.dashboard.today') }}</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">{{ $todayDocuments }}</p>
        </article>

        <article class="app-card">
            <p class="flex items-center gap-2 text-xs uppercase tracking-[0.12em] text-slate-500"><x-icon name="attachments" class="h-5 w-5" />{{ __('ui.dashboard.attachments') }}</p>
            <p class="mt-2 text-lg font-semibold {{ $attachmentsEnabled ? 'text-emerald-700' : 'text-amber-700' }}">
                {{ $attachmentsEnabled ? __('ui.dashboard.enabled') : __('ui.dashboard.disabled') }}
            </p>
        </article>

        <article class="quick-access-card">
            <div class="quick-access-orb" aria-hidden="true"></div>
            <div class="relative z-10 flex h-full flex-col gap-4">
                <div class="flex items-start gap-3">
                    <span class="quick-access-icon-wrap">
                        <x-icon name="quick-access" class="h-5 w-5" />
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs uppercase tracking-[0.12em] text-teal-800/80">{{ __('ui.dashboard.quick_access') }}</p>
                        <p class="mt-1 text-base font-bold text-slate-900">{{ __('ui.dashboard.quick_create') }}</p>
                    </div>
                </div>

                @if ($canCreateDocuments)
                    <a href="{{ route('documents.create') }}" class="quick-access-cta mt-auto">
                        <x-icon name="create" class="h-5 w-5" />
                        <span>{{ __('ui.nav.create') }}</span>
                        <x-icon name="arrow-right" class="h-5 w-5" />
                    </a>
                @else
                    <span class="quick-access-disabled mt-auto">{{ __('ui.dashboard.no_permission') }}</span>
                @endif
            </div>
        </article>
    </section>

    @if ($canViewDocuments)
        <section class="app-card">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-900">{{ __('ui.dashboard.recent_documents') }}</h3>
                <a href="{{ route('documents.index') }}" class="text-xs font-semibold text-teal-700">{{ __('ui.dashboard.view_all') }}</a>
            </div>

            <div class="space-y-3">
                @forelse (($recentDocuments ?? []) as $document)
                    <a href="{{ route('documents.show', $document) }}" class="block rounded-xl border border-slate-200 p-3 transition hover:border-teal-300 hover:bg-teal-50">
                        <p class="text-sm font-semibold text-slate-900">{{ $document->title }}</p>
                        <p class="mt-1 text-xs text-slate-500">{{ $document->prefix_code }} • {{ $document->category->name ?? '-' }} • {{ $document->created_at->format('d.m.Y H:i') }}</p>
                    </a>
                @empty
                    <p class="text-sm text-slate-500">{{ __('ui.dashboard.no_documents') }}</p>
                @endforelse
            </div>

            @if ($recentDocuments && $recentDocuments->count() > 0)
                <div class="mt-4">
                    {{ $recentDocuments->onEachSide(1)->links() }}
                </div>
            @endif
        </section>
    @endif
</x-app-layout>
