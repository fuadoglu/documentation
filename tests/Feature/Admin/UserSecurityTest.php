<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserSecurityTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        $admin = User::factory()->create([
            'email' => 'admin@company.az',
        ]);

        Role::query()->firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::query()->firstOrCreate(['name' => 'employee', 'guard_name' => 'web']);
        $admin->assignRole('admin');

        return $admin;
    }

    public function test_admin_user_create_requires_strong_confirmed_password(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)
            ->from(route('admin.users.index'))
            ->post(route('admin.users.store'), [
                'name' => 'Employee One',
                'email' => 'employee.one@company.az',
                'password' => 'weakpass',
                'password_confirmation' => 'weakpass',
                'locale' => 'az',
                'role' => 'employee',
                'is_active' => '1',
            ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHasErrors('password');
        $this->assertDatabaseMissing('users', ['email' => 'employee.one@company.az']);
    }

    public function test_admin_user_create_does_not_leak_password_in_status_flash(): void
    {
        $admin = $this->createAdmin();
        $plainPassword = 'StrongPass123!';

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Employee Two',
            'email' => 'employee.two@company.az',
            'password' => $plainPassword,
            'password_confirmation' => $plainPassword,
            'locale' => 'az',
            'role' => 'employee',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('status', __('messages.status.user_created'));

        $user = User::query()->where('email', 'employee.two@company.az')->firstOrFail();
        $this->assertTrue(Hash::check($plainPassword, $user->password));
    }

    public function test_admin_password_reset_requires_strong_confirmed_password_and_does_not_leak_value(): void
    {
        $admin = $this->createAdmin();
        $user = User::factory()->create([
            'email' => 'employee.three@company.az',
        ]);
        $newPassword = 'ResetPass123!';

        $weakResponse = $this->actingAs($admin)
            ->from(route('admin.users.index'))
            ->post(route('admin.users.reset-password', $user), [
                'password' => '12345678',
                'password_confirmation' => '12345678',
            ]);

        $weakResponse->assertRedirect(route('admin.users.index'));
        $weakResponse->assertSessionHasErrors('password');

        $successResponse = $this->actingAs($admin)->post(route('admin.users.reset-password', $user), [
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
        ]);

        $successResponse->assertRedirect(route('admin.users.index'));
        $successResponse->assertSessionHasNoErrors();
        $successResponse->assertSessionHas('status', __('messages.status.user_password_reset'));
        $this->assertTrue(Hash::check($newPassword, $user->fresh()->password));
    }
}
