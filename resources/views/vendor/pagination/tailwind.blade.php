@if ($paginator->hasPages())
    @php
        $from = $paginator->firstItem() ?? 0;
        $to = $paginator->lastItem() ?? 0;
        $total = $paginator instanceof \Illuminate\Pagination\LengthAwarePaginator ? $paginator->total() : $to;
    @endphp

    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center justify-between gap-2">
        <div class="flex w-full items-center justify-between gap-2 sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="app-pagination-link app-pagination-link-disabled">
                    @lang('pagination.previous')
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="app-pagination-link">
                    @lang('pagination.previous')
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="app-pagination-link">
                    @lang('pagination.next')
                </a>
            @else
                <span class="app-pagination-link app-pagination-link-disabled">
                    @lang('pagination.next')
                </span>
            @endif
        </div>

        <div class="hidden w-full items-center justify-between gap-3 sm:flex">
            <p class="text-xs font-medium text-slate-500">
                {{ __('ui.common.pagination_meta', ['from' => $from, 'to' => $to, 'total' => $total]) }}
            </p>

            <div class="flex items-center gap-1">
                @if ($paginator->onFirstPage())
                    <span class="app-pagination-link app-pagination-link-disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                        <x-icon name="chevron-left" class="h-4 w-4" />
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="app-pagination-link" aria-label="@lang('pagination.previous')">
                        <x-icon name="chevron-left" class="h-4 w-4" />
                    </a>
                @endif

                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="px-2 text-xs text-slate-400">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page === $paginator->currentPage())
                                <span aria-current="page" class="app-pagination-link app-pagination-link-active">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="app-pagination-link" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="app-pagination-link" aria-label="@lang('pagination.next')">
                        <x-icon name="chevron-right" class="h-4 w-4" />
                    </a>
                @else
                    <span class="app-pagination-link app-pagination-link-disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                        <x-icon name="chevron-right" class="h-4 w-4" />
                    </span>
                @endif
            </div>
        </div>
    </nav>
@endif
