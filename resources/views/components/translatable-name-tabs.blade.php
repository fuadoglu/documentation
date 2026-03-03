@props([
    'locales' => ['az', 'en'],
    'values' => [],
    'name' => 'name_translations',
    'maxLength' => 150,
    'label' => null,
])

@php
    $locales = array_values($locales);
    if ($locales === []) {
        $locales = ['az', 'en'];
    }
    $fallbackLocale = config('app.locale', 'az');
    $initialLocale = in_array($fallbackLocale, $locales, true) ? $fallbackLocale : ($locales[0] ?? 'az');
    $label = $label ?: __('ui.common.translations');
@endphp

<div x-data="{ tab: '{{ $initialLocale }}' }" class="space-y-2">
    <p class="app-label">{{ $label }}</p>

    <div class="flex flex-wrap gap-2">
        @foreach ($locales as $locale)
            @php
                $localeKey = 'ui.common.language_'.$locale;
                $localeLabel = __($localeKey);
                if ($localeLabel === $localeKey) {
                    $localeLabel = strtoupper($locale);
                }
                $supportsFlag = in_array($locale, ['az', 'en'], true);
            @endphp
            <button
                type="button"
                @click="tab = '{{ $locale }}'"
                class="inline-flex min-h-11 items-center gap-2 rounded-lg border px-3 py-2.5 text-sm font-semibold leading-none transition"
                :class="tab === '{{ $locale }}' ? 'border-teal-600 bg-teal-50 text-teal-700' : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-100'"
            >
                @if ($supportsFlag)
                    <x-icon :name="'flag-'.$locale" class="h-5 w-5 shrink-0 rounded-sm" />
                @endif
                <span>{{ $localeLabel }}</span>
            </button>
        @endforeach
    </div>

    @foreach ($locales as $locale)
        @php
            $localeKey = 'ui.common.language_'.$locale;
            $localeLabel = __($localeKey);
            if ($localeLabel === $localeKey) {
                $localeLabel = strtoupper($locale);
            }
        @endphp
        <div x-show="tab === '{{ $locale }}'" x-cloak>
            <input
                name="{{ $name }}[{{ $locale }}]"
                value="{{ $values[$locale] ?? '' }}"
                class="app-input"
                maxlength="{{ $maxLength }}"
                placeholder="{{ __('ui.common.name_in_language', ['language' => $localeLabel]) }}"
            >
        </div>
    @endforeach
</div>
