<x-app-layout>
    <x-slot name="header">
        {{ __('ui.admin.permissions.title') }}
    </x-slot>

    @php
        $groupLabel = static function (string $group): string {
            $key = "ui.admin.permissions.groups.$group";
            $translated = __($key);

            return $translated === $key
                ? ucfirst(str_replace(['.', '_'], ' ', $group))
                : $translated;
        };

        $permissionLabel = static function (string $permission): string {
            $parts = explode('.', $permission, 2);
            $module = $parts[0] ?? null;
            $action = $parts[1] ?? null;

            if (! $module || ! $action) {
                return ucfirst(str_replace(['.', '_'], ' ', $permission));
            }

            $key = "ui.admin.permissions.items.$module.$action";
            $translated = __($key);

            return $translated === $key
                ? ucfirst(str_replace(['.', '_'], ' ', $permission))
                : $translated;
        };
    @endphp

    <section class="app-card mb-4">
        <div class="mb-4">
            <h3 class="text-base font-semibold text-slate-900">{{ __('ui.admin.permissions.employee_title') }}</h3>
            <p class="mt-1 text-sm text-slate-500">{{ __('ui.admin.permissions.employee_hint') }}</p>
        </div>

        <form method="POST" action="{{ route('admin.permissions.employee.update') }}" class="space-y-6">
            @csrf
            @method('PUT')

            @forelse ($permissionGroups as $group => $permissions)
                <div class="rounded-xl border border-slate-200 p-4">
                    <p class="mb-3 text-xs font-semibold uppercase tracking-[0.12em] text-slate-600">{{ $groupLabel($group) }}</p>

                    <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($permissions as $permission)
                            <label class="flex items-start gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700">
                                <input
                                    type="checkbox"
                                    name="permissions[]"
                                    value="{{ $permission->name }}"
                                    class="rounded border-slate-300 text-teal-600 focus:ring-teal-500"
                                    @checked(in_array($permission->name, $assigned, true))
                                >
                                <span class="break-words leading-tight">{{ $permissionLabel($permission->name) }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @empty
                <p class="text-sm text-slate-500">{{ __('ui.admin.permissions.not_found') }}</p>
            @endforelse

            <div>
                <button type="submit" class="app-button-primary">{{ __('ui.admin.permissions.save_employee') }}</button>
            </div>
        </form>
    </section>

    <section class="app-card">
        <div class="mb-4">
            <h3 class="text-base font-semibold text-slate-900">{{ __('ui.admin.permissions.admin_title') }}</h3>
            <p class="mt-1 text-sm text-slate-500">{{ __('ui.admin.permissions.admin_hint') }}</p>
        </div>

        @forelse ($permissionGroups as $group => $permissions)
            <div class="mb-4 rounded-xl border border-slate-200 p-4">
                <p class="mb-3 text-xs font-semibold uppercase tracking-[0.12em] text-slate-600">{{ $groupLabel($group) }}</p>

                <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($permissions as $permission)
                        <label class="flex items-start gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700">
                            <input
                                type="checkbox"
                                disabled
                                class="rounded border-slate-300 text-teal-600"
                                @checked(in_array($permission->name, $adminAssigned, true))
                            >
                            <span class="break-words leading-tight">{{ $permissionLabel($permission->name) }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        @empty
            <p class="text-sm text-slate-500">{{ __('ui.admin.permissions.not_found') }}</p>
        @endforelse
    </section>
</x-app-layout>
