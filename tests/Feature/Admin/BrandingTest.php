<?php

namespace Tests\Feature\Admin;

use App\Models\BrandingSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BrandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_upload_svg_logo(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create();
        Role::query()->firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->assignRole('admin');

        BrandingSetting::current();

        $svg = UploadedFile::fake()->createWithContent(
            'logo.svg',
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><rect width="100" height="100" fill="#0F766E"/></svg>'
        );

        $response = $this->actingAs($admin)->put(route('admin.branding.update'), [
            'company_name' => 'ECO DC',
            'allowed_login_domain' => 'company.az',
            'attachments_enabled' => '1',
            'primary_color' => '#0F766E',
            'secondary_color' => '#0B132B',
            'timezone' => 'Asia/Baku',
            'logo' => $svg,
        ]);

        $response->assertRedirect(route('admin.branding.edit'));

        $settings = BrandingSetting::current()->fresh();

        $this->assertNotNull($settings->logo_path);
        $this->assertStringEndsWith('.svg', $settings->logo_path);
        Storage::disk('public')->assertExists($settings->logo_path);
    }

    public function test_admin_cannot_upload_unsafe_svg_logo(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create();
        Role::query()->firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->assignRole('admin');

        BrandingSetting::current();

        $unsafeSvg = UploadedFile::fake()->createWithContent(
            'logo.svg',
            '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'
        );

        $response = $this->actingAs($admin)
            ->from(route('admin.branding.edit'))
            ->put(route('admin.branding.update'), [
                'company_name' => 'ECO DC',
                'allowed_login_domain' => 'company.az',
                'attachments_enabled' => '1',
                'primary_color' => '#0F766E',
                'secondary_color' => '#0B132B',
                'timezone' => 'Asia/Baku',
                'logo' => $unsafeSvg,
            ]);

        $response->assertRedirect(route('admin.branding.edit'));
        $response->assertSessionHasErrors('logo');

        $settings = BrandingSetting::current()->fresh();
        $this->assertNull($settings->logo_path);
    }

    public function test_admin_can_upload_favicon(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create();
        Role::query()->firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->assignRole('admin');

        BrandingSetting::current();

        $favicon = UploadedFile::fake()->image('favicon.png', 32, 32);

        $response = $this->actingAs($admin)->put(route('admin.branding.update'), [
            'company_name' => 'ECO DC',
            'allowed_login_domain' => 'company.az',
            'attachments_enabled' => '1',
            'primary_color' => '#0F766E',
            'secondary_color' => '#0B132B',
            'timezone' => 'Asia/Baku',
            'favicon' => $favicon,
        ]);

        $response->assertRedirect(route('admin.branding.edit'));

        $settings = BrandingSetting::current()->fresh();

        $this->assertNotNull($settings->favicon_path);
        Storage::disk('public')->assertExists($settings->favicon_path);
    }

    public function test_branding_logo_route_returns_not_found_when_logo_is_missing(): void
    {
        BrandingSetting::query()->updateOrCreate(
            ['id' => 1],
            [
                'company_name' => 'ECO DC',
                'allowed_login_domain' => 'company.az',
                'attachments_enabled' => true,
                'logo_path' => null,
            ]
        );

        $this->get(route('branding.logo'))->assertNotFound();
    }

    public function test_branding_logo_route_returns_file_when_logo_exists(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('branding/current-logo.svg', '<svg xmlns="http://www.w3.org/2000/svg"></svg>');

        BrandingSetting::query()->updateOrCreate(
            ['id' => 1],
            [
                'company_name' => 'ECO DC',
                'allowed_login_domain' => 'company.az',
                'attachments_enabled' => true,
                'logo_path' => 'branding/current-logo.svg',
            ]
        );

        $this->get(route('branding.logo'))->assertOk();
    }

    public function test_branding_favicon_route_returns_not_found_when_favicon_is_missing(): void
    {
        BrandingSetting::query()->updateOrCreate(
            ['id' => 1],
            [
                'company_name' => 'ECO DC',
                'allowed_login_domain' => 'company.az',
                'attachments_enabled' => true,
                'favicon_path' => null,
            ]
        );

        $this->get(route('branding.favicon'))->assertNotFound();
    }

    public function test_branding_favicon_route_returns_file_when_favicon_exists(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('branding/current-favicon.png', 'png-binary');

        BrandingSetting::query()->updateOrCreate(
            ['id' => 1],
            [
                'company_name' => 'ECO DC',
                'allowed_login_domain' => 'company.az',
                'attachments_enabled' => true,
                'favicon_path' => 'branding/current-favicon.png',
            ]
        );

        $this->get(route('branding.favicon'))->assertOk();
    }

    public function test_admin_can_update_company_timezone(): void
    {
        $admin = User::factory()->create();
        Role::query()->firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->assignRole('admin');

        BrandingSetting::current();

        $response = $this->actingAs($admin)->put(route('admin.branding.update'), [
            'company_name' => 'ECO DC',
            'allowed_login_domain' => 'company.az',
            'attachments_enabled' => '1',
            'primary_color' => '#0F766E',
            'secondary_color' => '#0B132B',
            'timezone' => 'Asia/Baku',
        ]);

        $response->assertRedirect(route('admin.branding.edit'));

        $this->assertDatabaseHas('branding_settings', [
            'id' => 1,
            'timezone' => 'Asia/Baku',
        ]);
    }

    public function test_layout_uses_branding_colors_for_theme_variables(): void
    {
        $user = User::factory()->create();

        BrandingSetting::query()->updateOrCreate(
            ['id' => 1],
            [
                'company_name' => 'ECO DC',
                'allowed_login_domain' => 'company.az',
                'attachments_enabled' => true,
                'primary_color' => '#123456',
                'secondary_color' => '#654321',
                'timezone' => 'Asia/Baku',
            ]
        );

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('<meta name="theme-color" content="#123456">', false);
        $response->assertSee(route('branding.theme'), false);

        $themeResponse = $this->actingAs($user)->get(route('branding.theme'));
        $themeResponse->assertOk();
        $themeResponse->assertSee('--color-brand: #123456;', false);
        $themeResponse->assertSee('--color-brand-dark: #654321;', false);
    }
}
