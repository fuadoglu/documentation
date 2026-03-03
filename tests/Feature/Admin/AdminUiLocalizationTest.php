<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminUiLocalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_users_page_renders_locale_dropdown_field(): void
    {
        $admin = User::factory()->create();
        Role::query()->firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get(route('admin.users.index'));

        $response->assertOk();
        $response->assertSee('name="locale"', false);
        $response->assertSee(__('ui.common.language_az'));
        $response->assertSee(__('ui.common.language_en'));
    }

    public function test_admin_can_create_category_with_multilingual_names(): void
    {
        $admin = User::factory()->create();
        Role::query()->firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->post(route('admin.categories.store'), [
            'code' => 'HR',
            'name_translations' => [
                'az' => 'İnsan resursları',
                'en' => 'Human Resources',
            ],
            'sort_order' => 10,
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('admin.categories.index'));

        $category = Category::query()->where('code', 'HR')->firstOrFail();

        $this->assertSame('İnsan resursları', $category->name_translations['az']);
        $this->assertSame('Human Resources', $category->name_translations['en']);

        app()->setLocale('en');
        $this->assertSame('Human Resources', $category->fresh()->name);
    }
}
