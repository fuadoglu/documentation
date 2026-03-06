@props([
    'transparent' => false,
])

@php
    $currentLocale = app()->getLocale();
    $supportedLocales = config('app.available_locales', ['az', 'en']);
    if ($supportedLocales === []) {
        $supportedLocales = ['az', 'en'];
    }

    $locales = collect($supportedLocales)->map(function (string $locale): array {
        $labelKey = 'ui.common.language_'.$locale;
        $label = __($labelKey);

        if ($label === $labelKey) {
            $label = strtoupper($locale);
        }

        return [
            'code' => $locale,
            'label' => $label,
            'icon' => in_array($locale, ['az', 'en'], true) ? 'flag-'.$locale : null,
        ];
    })->values()->all();

    $current = collect($locales)->firstWhere('code', $currentLocale) ?? $locales[0];
    $triggerClass = $transparent
        ? 'lang-dropdown-trigger lang-dropdown-trigger-transparent px-2.5 sm:px-3'
        : 'lang-dropdown-trigger px-2.5 sm:px-3';
@endphp

<div x-data="{ open: false }" class="relative" @click.away="open = false" @keydown.escape.window="open = false">
    <button
        type="button"
        class="{{ $triggerClass }}"
        @click="open = !open"
        :aria-expanded="open.toString()"
        aria-haspopup="true"
        aria-label="{{ __('ui.common.language') }}"
    >
        @if ($current['icon'])
            <x-icon :name="$current['icon']" class="h-5 w-5 shrink-0 rounded-sm" />
        @endif
        <span class="hidden truncate sm:inline">{{ $current['label'] }}</span>
        <x-icon name="chevron-down" class="hidden h-5 w-5 shrink-0 transition sm:inline-flex" x-bind:class="{ 'rotate-180': open }" />
    </button>

    <div x-cloak :class="open ? 'block' : 'hidden'" class="lang-dropdown-panel">
        @foreach ($locales as $locale)
            <form method="POST" action="{{ route('locale.update') }}">
                @csrf
                <input type="hidden" name="locale" value="{{ $locale['code'] }}">
                <button
                    type="submit"
                    @click="open = false"
                    class="lang-dropdown-item {{ $currentLocale === $locale['code'] ? 'lang-dropdown-item-active' : '' }}"
                >
                    @if ($locale['icon'])
                        <x-icon :name="$locale['icon']" class="h-5 w-5 shrink-0 rounded-sm" />
                    @endif
                    <span>{{ $locale['label'] }}</span>
                </button>
            </form>
        @endforeach
    </div>
</div>
