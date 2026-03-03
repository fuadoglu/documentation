<x-app-layout>
    <x-slot name="header">
        {{ __('ui.admin.audit.title') }}
    </x-slot>

    <section class="app-card">
        <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
            <div>
                <label class="app-label" for="causer_id">{{ __('ui.admin.audit.user') }}</label>
                <select id="causer_id" name="causer_id" class="app-input">
                    <option value="">{{ __('ui.common.all') }}</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" @selected(($filters['causer_id'] ?? '') == $user->id)>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="app-label" for="module">{{ __('ui.admin.audit.module') }}</label>
                <select id="module" name="module" class="app-input">
                    <option value="">{{ __('ui.common.all') }}</option>
                    @foreach ($modules as $module)
                        @php
                            $moduleKey = 'ui.admin.audit.module_values.'.$module;
                            $moduleLabel = __($moduleKey);
                        @endphp
                        <option value="{{ $module }}" @selected(($filters['module'] ?? '') === $module)>{{ $moduleLabel === $moduleKey ? $module : $moduleLabel }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="app-label" for="event">{{ __('ui.admin.audit.event') }}</label>
                <select id="event" name="event" class="app-input">
                    <option value="">{{ __('ui.common.all') }}</option>
                    @foreach ($events as $event)
                        @php
                            $eventKey = 'ui.admin.audit.event_values.'.$event;
                            $eventLabel = __($eventKey);
                        @endphp
                        <option value="{{ $event }}" @selected(($filters['event'] ?? '') === $event)>{{ $eventLabel === $eventKey ? $event : $eventLabel }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="app-label" for="subject_id">{{ __('ui.admin.audit.subject_id') }}</label>
                <input id="subject_id" type="number" min="1" name="subject_id" value="{{ $filters['subject_id'] ?? '' }}" class="app-input" placeholder="{{ __('ui.admin.audit.subject_placeholder') }}">
            </div>

            <div>
                <label class="app-label" for="ip">{{ __('ui.admin.audit.ip') }}</label>
                <input id="ip" type="text" name="ip" value="{{ $filters['ip'] ?? '' }}" class="app-input" placeholder="{{ __('ui.admin.audit.ip_placeholder') }}">
            </div>

            <div>
                <label class="app-label" for="date_from">{{ __('ui.admin.audit.from_date') }}</label>
                <input id="date_from" type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="app-input">
            </div>

            <div>
                <label class="app-label" for="date_to">{{ __('ui.admin.audit.to_date') }}</label>
                <input id="date_to" type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="app-input">
            </div>

            <div class="app-action-group items-end sm:col-span-2 xl:col-span-3">
                <button class="app-button-primary w-full sm:w-auto" type="submit">{{ __('ui.common.filter') }}</button>
                <a href="{{ route('admin.audit-logs.index') }}" class="app-button-secondary w-full sm:w-auto">{{ __('ui.common.clear') }}</a>
                <a href="{{ route('admin.audit-logs.export', request()->query()) }}" class="app-button-secondary w-full sm:w-auto">{{ __('ui.admin.audit.csv_export') }}</a>
            </div>
        </form>
    </section>

    <section class="space-y-3">
        @forelse ($logs as $log)
            <article class="app-card">
                <div class="grid gap-2 text-sm sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <p class="text-xs text-slate-500">{{ __('ui.admin.audit.time') }}</p>
                        <p class="font-medium text-slate-900">{{ $log->created_at?->format('d.m.Y H:i:s') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500">{{ __('ui.admin.audit.user') }}</p>
                        <p class="font-medium text-slate-900">{{ $log->causer?->name ?? __('ui.admin.audit.system') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500">{{ __('ui.admin.audit.module') }}</p>
                        @php
                            $subjectType = class_basename($log->subject_type ?? '');
                            $subjectTypeKey = 'ui.admin.audit.module_values.'.$subjectType;
                            $subjectTypeLabel = __($subjectTypeKey);
                        @endphp
                        <p class="font-medium text-slate-900">
                            {{ $subjectType ? ($subjectTypeLabel === $subjectTypeKey ? $subjectType : $subjectTypeLabel) : __('ui.admin.audit.not_available') }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500">{{ __('ui.admin.audit.event') }}</p>
                        @php
                            $eventName = $log->event ?? '';
                            $eventNameKey = 'ui.admin.audit.event_values.'.$eventName;
                            $eventNameLabel = __($eventNameKey);
                        @endphp
                        <p class="font-medium text-slate-900">
                            {{ $eventName ? ($eventNameLabel === $eventNameKey ? $eventName : $eventNameLabel) : __('ui.admin.audit.not_available') }}
                        </p>
                    </div>
                </div>

                <div class="mt-3 rounded-lg border border-slate-200 bg-slate-50 p-3 text-sm text-slate-700">
                    {{ $log->description }}
                </div>
            </article>
        @empty
            <article class="app-card text-sm text-slate-500">{{ __('ui.admin.audit.not_found') }}</article>
        @endforelse
    </section>

    <div>
        {{ $logs->links() }}
    </div>
</x-app-layout>
