<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AuditLogger;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionController extends Controller
{
    public function index(): View
    {
        $permissions = Permission::query()
            ->orderBy('name')
            ->get(['id', 'name', 'guard_name']);

        $adminRole = $this->ensureAdminRoleHasAllPermissions($permissions);
        $employeeRole = Role::query()->firstOrCreate(['name' => 'employee', 'guard_name' => 'web']);

        $permissionGroups = $permissions
            ->groupBy(fn (Permission $permission) => explode('.', $permission->name)[0]);

        return view('admin.permissions.index', [
            'adminRole' => $adminRole,
            'employeeRole' => $employeeRole,
            'permissionGroups' => $permissionGroups,
            'adminAssigned' => $adminRole->permissions->pluck('name')->all(),
            'assigned' => $employeeRole->permissions->pluck('name')->all(),
        ]);
    }

    public function updateEmployee(Request $request): RedirectResponse
    {
        $permissions = Permission::query()->orderBy('name')->get(['id', 'name', 'guard_name']);
        $this->ensureAdminRoleHasAllPermissions($permissions);

        $employeeRole = Role::query()->firstOrCreate(['name' => 'employee', 'guard_name' => 'web']);

        $validated = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $permissionNames = collect($validated['permissions'] ?? [])
            ->filter(fn ($name) => is_string($name))
            ->values()
            ->all();

        $employeeRole->syncPermissions($permissionNames);

        AuditLogger::event($request, $employeeRole, 'updated', __('messages.audit.employee_permissions_updated'), [
            'permission_count' => count($permissionNames),
        ]);

        return redirect()
            ->route('admin.permissions.index')
            ->with('status', __('messages.status.employee_permissions_updated'));
    }

    private function ensureAdminRoleHasAllPermissions(Collection $permissions): Role
    {
        $adminRole = Role::query()->firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions($permissions);

        return $adminRole;
    }
}
