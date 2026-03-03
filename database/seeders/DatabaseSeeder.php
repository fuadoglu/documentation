<?php

namespace Database\Seeders;

use App\Models\BrandingSetting;
use App\Models\Category;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        BrandingSetting::query()->updateOrCreate(
            ['id' => 1],
            [
                'company_name' => env('APP_NAME', 'ECO DC'),
                'allowed_login_domain' => env('ALLOWED_LOGIN_DOMAIN', 'company.az'),
                'attachments_enabled' => true,
                'primary_color' => '#0F766E',
                'secondary_color' => '#0B132B',
                'timezone' => env('APP_TIMEZONE', 'UTC'),
            ]
        );

        $permissions = [
            'documents.view',
            'documents.create',
            'documents.download',
            'users.view',
            'users.create',
            'users.update',
            'users.activate',
            'categories.view',
            'categories.create',
            'categories.update',
            'categories.delete',
            'folders.view',
            'folders.create',
            'folders.update',
            'folders.delete',
            'branding.view',
            'branding.update',
            'audit.view',
        ];

        foreach ($permissions as $permission) {
            Permission::query()->firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $adminRole = Role::query()->firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $employeeRole = Role::query()->firstOrCreate(['name' => 'employee', 'guard_name' => 'web']);

        $adminRole->syncPermissions(Permission::all());
        $employeeRole->syncPermissions([
            'documents.view',
            'documents.create',
            'documents.download',
        ]);

        $adminEmail = env('ADMIN_EMAIL', 'admin@company.az');
        $adminPassword = env('ADMIN_PASSWORD', 'Admin@12345');

        $admin = User::query()->firstOrCreate(
            ['email' => $adminEmail],
            [
                'name' => 'System Admin',
                'password' => Hash::make($adminPassword),
                'locale' => env('APP_LOCALE', 'az'),
                'is_active' => true,
                'must_change_password' => false,
            ]
        );

        $admin->syncRoles([$adminRole->name]);

        Category::query()->firstOrCreate(
            ['code' => 'GEN'],
            [
                'name' => 'Ümumi',
                'name_translations' => [
                    'az' => 'Ümumi',
                    'en' => 'General',
                ],
                'is_active' => true,
                'sort_order' => 1,
                'created_by' => $admin->id,
            ]
        );

        Folder::query()->firstOrCreate(
            ['code' => 'MAIN'],
            [
                'name' => 'Əsas qovluq',
                'name_translations' => [
                    'az' => 'Əsas qovluq',
                    'en' => 'Main folder',
                ],
                'is_active' => true,
                'sort_order' => 1,
                'created_by' => $admin->id,
            ]
        );
    }
}
