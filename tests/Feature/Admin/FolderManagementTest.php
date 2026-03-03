<?php

namespace Tests\Feature\Admin;

use App\Models\Folder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FolderManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_root_folder_without_parent(): void
    {
        $admin = User::factory()->create();
        Role::query()->firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->post(route('admin.folders.store'), [
            'code' => 'ROOT01',
            'name_translations' => [
                'az' => 'Əsas qovluq',
                'en' => 'Main folder',
            ],
            'parent_id' => null,
            'sort_order' => 0,
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('admin.folders.index'));
        $this->assertDatabaseHas('folders', [
            'code' => 'ROOT01',
            'parent_id' => null,
        ]);
    }

    public function test_admin_can_create_child_folder_under_parent_folder(): void
    {
        $admin = User::factory()->create();
        Role::query()->firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->assignRole('admin');

        $parent = Folder::query()->create([
            'code' => 'PARENT',
            'name' => 'Ana qovluq',
            'name_translations' => [
                'az' => 'Ana qovluq',
                'en' => 'Parent folder',
            ],
            'parent_id' => null,
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.folders.store'), [
            'code' => 'CHILD',
            'name_translations' => [
                'az' => 'Alt qovluq',
                'en' => 'Child folder',
            ],
            'parent_id' => $parent->id,
            'sort_order' => 1,
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('admin.folders.index'));
        $this->assertDatabaseHas('folders', [
            'code' => 'CHILD',
            'parent_id' => $parent->id,
        ]);
    }

    public function test_admin_cannot_set_child_folder_as_parent(): void
    {
        $admin = User::factory()->create();
        Role::query()->firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->assignRole('admin');

        $parent = Folder::query()->create([
            'code' => 'PARENT',
            'name' => 'Ana qovluq',
            'name_translations' => [
                'az' => 'Ana qovluq',
                'en' => 'Parent folder',
            ],
            'parent_id' => null,
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $child = Folder::query()->create([
            'code' => 'CHILD',
            'name' => 'Alt qovluq',
            'name_translations' => [
                'az' => 'Alt qovluq',
                'en' => 'Child folder',
            ],
            'parent_id' => $parent->id,
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.folders.store'), [
            'code' => 'CHILD2',
            'name_translations' => [
                'az' => 'Yanlış alt qovluq',
                'en' => 'Invalid child folder',
            ],
            'parent_id' => $child->id,
            'sort_order' => 2,
            'is_active' => 1,
        ]);

        $response->assertSessionHasErrors('parent_id');
        $this->assertDatabaseMissing('folders', [
            'code' => 'CHILD2',
        ]);
    }
}
