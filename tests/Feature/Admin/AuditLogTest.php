<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_audit_logs_page(): void
    {
        $admin = User::factory()->create();

        Role::query()->firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get(route('admin.audit-logs.index'))
            ->assertOk();
    }

    public function test_admin_can_export_audit_logs_csv(): void
    {
        $admin = User::factory()->create();

        Role::query()->firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)
            ->get(route('admin.audit-logs.export'));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $response->assertHeader('content-disposition');
    }

    public function test_employee_cannot_view_audit_logs_page(): void
    {
        $employee = User::factory()->create();

        $this->actingAs($employee)
            ->get(route('admin.audit-logs.index'))
            ->assertForbidden();
    }
}
