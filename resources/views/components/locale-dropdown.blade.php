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
    $panelId = 'locale-dropdown-panel-'.\Illuminate\Support\Str::uuid()->toString();
@endphp

<div data-locale-dropdown class="relative">
    <button
        type="button"
        class="{{ $triggerClass }}"
        data-locale-trigger
        aria-expanded="false"
        aria-haspopup="true"
        aria-controls="{{ $panelId }}"
        aria-label="{{ __('ui.common.language') }}"
    >
        @if ($current['icon'])
            <x-icon :name="$current['icon']" class="h-5 w-5 shrink-0 rounded-sm" />
        @endif
        <span class="hidden truncate sm:inline">{{ $current['label'] }}</span>
        <x-icon name="chevron-down" data-locale-chevron class="hidden h-5 w-5 shrink-0 transition sm:inline-flex" />
    </button>

    <div id="{{ $panelId }}" data-locale-panel class="lang-dropdown-panel hidden">
        @foreach ($locales as $locale)
            <form method="POST" action="{{ route('locale.update') }}" data-locale-form>
                @csrf
                <input type="hidden" name="locale" value="{{ $locale['code'] }}">
                <button
                    type="submit"
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
