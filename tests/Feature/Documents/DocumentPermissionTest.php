<?php

namespace Tests\Feature\Documents;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class DocumentPermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_without_documents_view_permission_cannot_access_documents_index(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('documents.index'))
            ->assertForbidden();
    }

    public function test_user_with_documents_view_permission_can_access_documents_index(): void
    {
        $user = User::factory()->create();

        Permission::query()->firstOrCreate(['name' => 'documents.view', 'guard_name' => 'web']);
        $user->givePermissionTo('documents.view');

        $this->actingAs($user)
            ->get(route('documents.index'))
            ->assertOk();
    }

    public function test_authenticated_document_pages_are_not_cacheable(): void
    {
        $user = User::factory()->create();
        Permission::query()->firstOrCreate(['name' => 'documents.view', 'guard_name' => 'web']);
        $user->givePermissionTo('documents.view');

        $response = $this->actingAs($user)
            ->get(route('documents.index'))
            ->assertOk()
            ->assertHeader('Pragma', 'no-cache');

        $cacheControl = (string) $response->headers->get('Cache-Control');
        $this->assertStringContainsString('no-store', $cacheControl);
        $this->assertStringContainsString('no-cache', $cacheControl);
        $this->assertStringContainsString('must-revalidate', $cacheControl);
    }
}
