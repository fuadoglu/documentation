<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PermissionManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_permissions_page_and_update_employee_permissions(): void
    {
        $admin = User::factory()->create();

        $adminRole = Role::query()->firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::query()->firstOrCreate(['name' => 'employee', 'guard_name' => 'web']);
        $admin->assignRole($adminRole);

        Permission::query()->firstOrCreate(['name' => 'documents.view', 'guard_name' => 'web']);
        Permission::query()->firstOrCreate(['name' => 'documents.create', 'guard_name' => 'web']);

        $this->actingAs($admin)
            ->get(route('admin.permissions.index'))
            ->assertOk()
            ->assertSeeText(__('ui.admin.permissions.groups.documents'))
            ->assertSeeText(__('ui.admin.permissions.items.documents.view'));

        $this->actingAs($admin)
            ->put(route('admin.permissions.employee.update'), [
                'permissions' => ['documents.view'],
            ])
            ->assertRedirect(route('admin.permissions.index'));

        $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

        $this->assertTrue($employeeRole->hasPermissionTo('documents.view'));
        $this->assertFalse($employeeRole->hasPermissionTo('documents.create'));
    }

    public function test_employee_cannot_access_permissions_page(): void
    {
        $employee = User::factory()->create();

        $this->actingAs($employee)
            ->get(route('admin.permissions.index'))
            ->assertForbidden();
    }
}
