<?php

namespace Tests\Feature\Auth;

use App\Models\BrandingSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_login_screen_shows_company_name_when_logo_is_missing(): void
    {
        BrandingSetting::query()->updateOrCreate(
            ['id' => 1],
            [
                'company_name' => 'Test Company',
                'allowed_login_domain' => 'company.az',
                'attachments_enabled' => true,
                'logo_path' => null,
            ]
        );

        $this->get('/login')
            ->assertOk()
            ->assertSeeText('Test Company');
    }

    public function test_login_screen_uses_logo_when_logo_exists(): void
    {
        BrandingSetting::query()->updateOrCreate(
            ['id' => 1],
            [
                'company_name' => 'Test Company',
                'allowed_login_domain' => 'company.az',
                'attachments_enabled' => true,
                'logo_path' => 'branding/logo.svg',
            ]
        );

        $this->get('/login')
            ->assertOk()
            ->assertSee(route('branding.logo', [], false), false);
    }

    public function test_active_user_can_login_with_allowed_domain(): void
    {
        $user = User::factory()->create([
            'email' => 'employee@company.az',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect('/dashboard');
    }

    public function test_user_with_disallowed_domain_cannot_login(): void
    {
        User::factory()->create([
            'email' => 'employee@example.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => 'employee@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_inactive_user_cannot_login(): void
    {
        User::factory()->create([
            'email' => 'inactive@company.az',
            'password' => Hash::make('password'),
            'is_active' => false,
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => 'inactive@company.az',
            'password' => 'password',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
