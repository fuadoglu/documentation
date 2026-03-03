<?php

namespace Tests\Feature\Documents;

use App\Models\BrandingSetting;
use App\Models\Category;
use App\Models\Document;
use App\Models\DocumentAttachment;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DocumentStoreTest extends TestCase
{
    use RefreshDatabase;

    private function grantDocumentPermissions(User $user): void
    {
        foreach (['documents.view', 'documents.create', 'documents.download'] as $permissionName) {
            Permission::query()->firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
        }

        $user->givePermissionTo(['documents.view', 'documents.create', 'documents.download']);
    }

    public function test_document_prefix_is_generated_with_expected_format(): void
    {
        $user = User::factory()->create();
        $this->grantDocumentPermissions($user);

        $category = Category::query()->create([
            'code' => 'HR',
            'name' => 'İnsan Resursları',
            'is_active' => true,
            'created_by' => $user->id,
        ]);

        $folder = Folder::query()->create([
            'code' => 'MAIN',
            'name' => 'Əsas',
            'is_active' => true,
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)->post('/documents', [
            'title' => 'Müqavilə',
            'category_id' => $category->id,
            'folder_id' => $folder->id,
        ])->assertRedirect();

        $this->actingAs($user)->post('/documents', [
            'title' => 'Müqavilə 2',
            'category_id' => $category->id,
            'folder_id' => $folder->id,
        ])->assertRedirect();

        $documents = Document::query()->orderBy('id')->get();

        $this->assertCount(2, $documents);
        $this->assertMatchesRegularExpression('/^ECP-MAIN\/HR-\d{4}\/0001$/', $documents[0]->prefix_code);
        $this->assertMatchesRegularExpression('/^ECP-MAIN\/HR-\d{4}\/0002$/', $documents[1]->prefix_code);
    }

    public function test_prefix_preview_is_generated_realtime_from_selected_folder_and_category(): void
    {
        $user = User::factory()->create();
        $this->grantDocumentPermissions($user);

        $category = Category::query()->create([
            'code' => 'HR',
            'name' => 'İnsan Resursları',
            'is_active' => true,
            'created_by' => $user->id,
        ]);

        $folder = Folder::query()->create([
            'code' => 'MAIN',
            'name' => 'Əsas',
            'is_active' => true,
            'created_by' => $user->id,
        ]);

        $year = now()->year;

        $this->actingAs($user)
            ->get(route('documents.prefix-preview', [
                'category_id' => $category->id,
                'folder_id' => $folder->id,
            ]))
            ->assertOk()
            ->assertJsonPath('prefix_code', "ECP-MAIN/HR-{$year}/0001");

        $this->actingAs($user)->post('/documents', [
            'title' => 'Birinci sənəd',
            'category_id' => $category->id,
            'folder_id' => $folder->id,
        ])->assertRedirect();

        $this->actingAs($user)
            ->get(route('documents.prefix-preview', [
                'category_id' => $category->id,
                'folder_id' => $folder->id,
            ]))
            ->assertOk()
            ->assertJsonPath('prefix_code', "ECP-MAIN/HR-{$year}/0002");
    }

    public function test_file_upload_is_blocked_when_attachments_feature_is_disabled(): void
    {
        $user = User::factory()->create();
        $this->grantDocumentPermissions($user);

        BrandingSetting::query()->updateOrCreate(
            ['id' => 1],
            [
                'company_name' => 'ECO DC',
                'allowed_login_domain' => 'company.az',
                'attachments_enabled' => false,
            ]
        );

        $category = Category::query()->create([
            'code' => 'FIN',
            'name' => 'Maliyyə',
            'is_active' => true,
            'created_by' => $user->id,
        ]);

        $folder = Folder::query()->create([
            'code' => 'ARCH',
            'name' => 'Arxiv',
            'is_active' => true,
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->post('/documents', [
            'title' => 'Qadağan test',
            'category_id' => $category->id,
            'folder_id' => $folder->id,
            'file' => UploadedFile::fake()->create('test.pdf', 100),
        ]);

        $response->assertSessionHasErrors('file');
        $this->assertDatabaseCount('documents', 0);
    }

    public function test_employee_cannot_open_or_download_other_employee_document(): void
    {
        Storage::fake('local');

        $owner = User::factory()->create();
        $other = User::factory()->create();
        $this->grantDocumentPermissions($owner);
        $this->grantDocumentPermissions($other);

        $category = Category::query()->create([
            'code' => 'OPS',
            'name' => 'Əməliyyat',
            'is_active' => true,
            'created_by' => $owner->id,
        ]);

        $folder = Folder::query()->create([
            'code' => 'MAIN',
            'name' => 'Əsas',
            'is_active' => true,
            'created_by' => $owner->id,
        ]);

        $document = Document::query()->create([
            'prefix_code' => 'ECP-MAIN/OPS-2026/0001',
            'title' => 'Gizli sənəd',
            'category_id' => $category->id,
            'folder_id' => $folder->id,
            'created_by' => $owner->id,
        ]);

        Storage::disk('local')->put('documents/2026/03/file.pdf', 'content');

        $attachment = DocumentAttachment::query()->create([
            'document_id' => $document->id,
            'original_name' => 'file.pdf',
            'stored_name' => 'file.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 7,
            'disk' => 'local',
            'file_path' => 'documents/2026/03/file.pdf',
            'uploaded_by' => $owner->id,
        ]);

        $this->actingAs($other)->get(route('documents.show', $document))->assertForbidden();
        $this->actingAs($other)->get(route('documents.attachments.download', [$document, $attachment]))->assertForbidden();
    }

    public function test_admin_can_access_documents_created_by_other_users(): void
    {
        $admin = User::factory()->create();
        $owner = User::factory()->create();

        foreach (['documents.view', 'documents.create', 'documents.download'] as $permissionName) {
            Permission::query()->firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
        }
        Role::query()->firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->assignRole('admin');
        $admin->givePermissionTo(['documents.view', 'documents.create', 'documents.download']);

        $category = Category::query()->create([
            'code' => 'FIN',
            'name' => 'Maliyyə',
            'is_active' => true,
            'created_by' => $owner->id,
        ]);

        $folder = Folder::query()->create([
            'code' => 'ARC',
            'name' => 'Arxiv',
            'is_active' => true,
            'created_by' => $owner->id,
        ]);

        $document = Document::query()->create([
            'prefix_code' => 'ECP-ARC/FIN-2026/0001',
            'title' => 'Admin baxış testi',
            'category_id' => $category->id,
            'folder_id' => $folder->id,
            'created_by' => $owner->id,
        ]);

        $this->actingAs($admin)->get(route('documents.show', $document))->assertOk();
        $this->actingAs($admin)->get(route('documents.index'))->assertSeeText('Admin baxış testi');
    }

    public function test_attachments_section_is_hidden_and_download_blocked_when_feature_is_disabled(): void
    {
        Storage::fake('local');

        $owner = User::factory()->create();
        $this->grantDocumentPermissions($owner);

        BrandingSetting::query()->updateOrCreate(
            ['id' => 1],
            [
                'company_name' => 'ECO DC',
                'allowed_login_domain' => 'company.az',
                'attachments_enabled' => false,
            ]
        );

        $category = Category::query()->create([
            'code' => 'OPS',
            'name' => 'Əməliyyat',
            'is_active' => true,
            'created_by' => $owner->id,
        ]);

        $folder = Folder::query()->create([
            'code' => 'MAIN',
            'name' => 'Əsas',
            'is_active' => true,
            'created_by' => $owner->id,
        ]);

        $document = Document::query()->create([
            'prefix_code' => 'ECP-MAIN/OPS-2026/0001',
            'title' => 'Əlavə deaktiv test',
            'category_id' => $category->id,
            'folder_id' => $folder->id,
            'created_by' => $owner->id,
        ]);

        Storage::disk('local')->put('documents/2026/03/disabled.pdf', 'content');

        $attachment = DocumentAttachment::query()->create([
            'document_id' => $document->id,
            'original_name' => 'disabled.pdf',
            'stored_name' => 'disabled.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 7,
            'disk' => 'local',
            'file_path' => 'documents/2026/03/disabled.pdf',
            'uploaded_by' => $owner->id,
        ]);

        $this->actingAs($owner)
            ->get(route('documents.show', $document))
            ->assertDontSeeText(__('ui.documents.attachments'));

        $this->actingAs($owner)
            ->get(route('documents.attachments.download', [$document, $attachment]))
            ->assertNotFound();
    }

    public function test_owner_can_update_and_delete_own_document(): void
    {
        $owner = User::factory()->create();
        $this->grantDocumentPermissions($owner);

        $category = Category::query()->create([
            'code' => 'OPS',
            'name' => 'Əməliyyat',
            'is_active' => true,
            'created_by' => $owner->id,
        ]);

        $newCategory = Category::query()->create([
            'code' => 'FIN',
            'name' => 'Maliyyə',
            'is_active' => true,
            'created_by' => $owner->id,
        ]);

        $folder = Folder::query()->create([
            'code' => 'MAIN',
            'name' => 'Əsas',
            'is_active' => true,
            'created_by' => $owner->id,
        ]);

        $newFolder = Folder::query()->create([
            'code' => 'ARC',
            'name' => 'Arxiv',
            'is_active' => true,
            'created_by' => $owner->id,
        ]);

        $createResponse = $this->actingAs($owner)->post('/documents', [
            'title' => 'İlkin sənəd',
            'category_id' => $category->id,
            'folder_id' => $folder->id,
        ]);

        $createResponse->assertRedirect();
        $document = Document::query()->latest('id')->firstOrFail();

        $updateResponse = $this->actingAs($owner)->put(route('documents.update', $document), [
            'title' => 'Yenilənmiş sənəd',
            'description' => 'Redaktə edildi',
            'category_id' => $newCategory->id,
            'folder_id' => $newFolder->id,
        ]);

        $updateResponse->assertRedirect(route('documents.show', $document));

        $document->refresh();
        $this->assertSame('Yenilənmiş sənəd', $document->title);
        $this->assertSame('Redaktə edildi', $document->description);
        $this->assertMatchesRegularExpression('/^ECP-ARC\/FIN-\d{4}\/0001$/', $document->prefix_code);

        $deleteResponse = $this->actingAs($owner)->delete(route('documents.destroy', $document));
        $deleteResponse->assertRedirect(route('documents.index'));
        $this->assertSoftDeleted('documents', ['id' => $document->id]);
    }

    public function test_owner_can_upload_document_versions_with_note_and_incremental_number(): void
    {
        Storage::fake('local');

        $owner = User::factory()->create();
        $this->grantDocumentPermissions($owner);

        $category = Category::query()->create([
            'code' => 'OPS',
            'name' => 'Əməliyyat',
            'is_active' => true,
            'created_by' => $owner->id,
        ]);

        $folder = Folder::query()->create([
            'code' => 'MAIN',
            'name' => 'Əsas',
            'is_active' => true,
            'created_by' => $owner->id,
        ]);

        $this->actingAs($owner)->post('/documents', [
            'title' => 'Versiya test sənədi',
            'category_id' => $category->id,
            'folder_id' => $folder->id,
        ])->assertRedirect();

        $document = Document::query()->latest('id')->firstOrFail();

        $this->actingAs($owner)->post(route('documents.versions.store', $document), [
            'file' => UploadedFile::fake()->create('v1.pdf', 100),
            'version_note' => 'İlkin versiya',
        ])->assertRedirect(route('documents.show', $document));

        $this->actingAs($owner)->post(route('documents.versions.store', $document), [
            'file' => UploadedFile::fake()->create('v2.pdf', 120),
            'version_note' => 'Məzmun yeniləndi',
        ])->assertRedirect(route('documents.show', $document));

        $this->assertDatabaseHas('document_attachments', [
            'document_id' => $document->id,
            'version_number' => 1,
            'version_note' => 'İlkin versiya',
        ]);

        $this->assertDatabaseHas('document_attachments', [
            'document_id' => $document->id,
            'version_number' => 2,
            'version_note' => 'Məzmun yeniləndi',
        ]);

        $attachments = DocumentAttachment::query()
            ->where('document_id', $document->id)
            ->orderBy('version_number')
            ->get();

        $this->assertCount(2, $attachments);
        $this->assertNotEmpty($attachments[0]->sha256);
        $this->assertNotEmpty($attachments[1]->sha256);
    }

    public function test_employee_cannot_upload_versions_to_other_employee_document(): void
    {
        Storage::fake('local');

        $owner = User::factory()->create();
        $other = User::factory()->create();
        $this->grantDocumentPermissions($owner);
        $this->grantDocumentPermissions($other);

        $category = Category::query()->create([
            'code' => 'OPS',
            'name' => 'Əməliyyat',
            'is_active' => true,
            'created_by' => $owner->id,
        ]);

        $folder = Folder::query()->create([
            'code' => 'MAIN',
            'name' => 'Əsas',
            'is_active' => true,
            'created_by' => $owner->id,
        ]);

        $document = Document::query()->create([
            'prefix_code' => 'ECP-MAIN/OPS-2026/0001',
            'title' => 'Sahib sənədi',
            'category_id' => $category->id,
            'folder_id' => $folder->id,
            'created_by' => $owner->id,
        ]);

        $this->actingAs($other)->post(route('documents.versions.store', $document), [
            'file' => UploadedFile::fake()->create('forbidden.pdf', 80),
            'version_note' => 'Qadağan versiya',
        ])->assertForbidden();

        $this->assertDatabaseMissing('document_attachments', [
            'document_id' => $document->id,
            'version_note' => 'Qadağan versiya',
        ]);
    }

    public function test_owner_can_delete_own_document_version(): void
    {
        Storage::fake('local');

        $owner = User::factory()->create();
        $this->grantDocumentPermissions($owner);

        $category = Category::query()->create([
            'code' => 'OPS',
            'name' => 'Əməliyyat',
            'is_active' => true,
            'created_by' => $owner->id,
        ]);

        $folder = Folder::query()->create([
            'code' => 'MAIN',
            'name' => 'Əsas',
            'is_active' => true,
            'created_by' => $owner->id,
        ]);

        $document = Document::query()->create([
            'prefix_code' => 'ECP-MAIN/OPS-2026/0001',
            'title' => 'Versiya silmə sənədi',
            'category_id' => $category->id,
            'folder_id' => $folder->id,
            'created_by' => $owner->id,
        ]);

        $path = 'documents/2026/03/version-delete.pdf';
        Storage::disk('local')->put($path, 'version-content');

        $attachment = DocumentAttachment::query()->create([
            'document_id' => $document->id,
            'version_number' => 1,
            'version_note' => 'Silinəcək versiya',
            'original_name' => 'version-delete.pdf',
            'stored_name' => 'version-delete.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 16,
            'sha256' => hash('sha256', 'version-content'),
            'disk' => 'local',
            'file_path' => $path,
            'uploaded_by' => $owner->id,
        ]);

        $this->actingAs($owner)
            ->delete(route('documents.versions.destroy', [$document, $attachment]))
            ->assertRedirect(route('documents.show', $document));

        $this->assertSoftDeleted('document_attachments', ['id' => $attachment->id]);
        Storage::disk('local')->assertMissing($path);
    }

    public function test_version_number_is_not_reused_after_soft_deleted_version(): void
    {
        Storage::fake('local');

        $owner = User::factory()->create();
        $this->grantDocumentPermissions($owner);

        $category = Category::query()->create([
            'code' => 'OPS',
            'name' => 'Əməliyyat',
            'is_active' => true,
            'created_by' => $owner->id,
        ]);

        $folder = Folder::query()->create([
            'code' => 'MAIN',
            'name' => 'Əsas',
            'is_active' => true,
            'created_by' => $owner->id,
        ]);

        $document = Document::query()->create([
            'prefix_code' => 'ECP-MAIN/OPS-2026/0001',
            'title' => 'Versiya təkrar test',
            'category_id' => $category->id,
            'folder_id' => $folder->id,
            'created_by' => $owner->id,
        ]);

        $this->actingAs($owner)->post(route('documents.versions.store', $document), [
            'file' => UploadedFile::fake()->create('v1.pdf', 100),
            'version_note' => 'Birinci versiya',
        ])->assertRedirect(route('documents.show', $document));

        $firstVersion = DocumentAttachment::query()
            ->where('document_id', $document->id)
            ->where('version_number', 1)
            ->firstOrFail();

        $this->actingAs($owner)
            ->delete(route('documents.versions.destroy', [$document, $firstVersion]))
            ->assertRedirect(route('documents.show', $document));

        $this->actingAs($owner)->post(route('documents.versions.store', $document), [
            'file' => UploadedFile::fake()->create('v2.pdf', 110),
            'version_note' => 'İkinci versiya',
        ])->assertRedirect(route('documents.show', $document));

        $this->assertDatabaseHas('document_attachments', [
            'document_id' => $document->id,
            'version_number' => 2,
            'version_note' => 'İkinci versiya',
            'deleted_at' => null,
        ]);
    }

    public function test_employee_cannot_delete_other_employee_document_version(): void
    {
        Storage::fake('local');

        $owner = User::factory()->create();
        $other = User::factory()->create();
        $this->grantDocumentPermissions($owner);
        $this->grantDocumentPermissions($other);

        $category = Category::query()->create([
            'code' => 'OPS',
            'name' => 'Əməliyyat',
            'is_active' => true,
            'created_by' => $owner->id,
        ]);

        $folder = Folder::query()->create([
            'code' => 'MAIN',
            'name' => 'Əsas',
            'is_active' => true,
            'created_by' => $owner->id,
        ]);

        $document = Document::query()->create([
            'prefix_code' => 'ECP-MAIN/OPS-2026/0001',
            'title' => 'Qadağan versiya silmə',
            'category_id' => $category->id,
            'folder_id' => $folder->id,
            'created_by' => $owner->id,
        ]);

        $path = 'documents/2026/03/forbidden-version-delete.pdf';
        Storage::disk('local')->put($path, 'forbidden-content');

        $attachment = DocumentAttachment::query()->create([
            'document_id' => $document->id,
            'version_number' => 1,
            'version_note' => 'Qorunan versiya',
            'original_name' => 'forbidden-version-delete.pdf',
            'stored_name' => 'forbidden-version-delete.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 18,
            'sha256' => hash('sha256', 'forbidden-content'),
            'disk' => 'local',
            'file_path' => $path,
            'uploaded_by' => $owner->id,
        ]);

        $this->actingAs($other)
            ->delete(route('documents.versions.destroy', [$document, $attachment]))
            ->assertForbidden();

        $this->assertDatabaseHas('document_attachments', ['id' => $attachment->id, 'deleted_at' => null]);
        Storage::disk('local')->assertExists($path);
    }

    public function test_admin_can_delete_other_users_document_version(): void
    {
        Storage::fake('local');

        $admin = User::factory()->create();
        $owner = User::factory()->create();

        Permission::query()->firstOrCreate(['name' => 'documents.view', 'guard_name' => 'web']);
        Role::query()->firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->assignRole('admin');
        $admin->givePermissionTo('documents.view');

        $category = Category::query()->create([
            'code' => 'OPS',
            'name' => 'Əməliyyat',
            'is_active' => true,
            'created_by' => $owner->id,
        ]);

        $folder = Folder::query()->create([
            'code' => 'MAIN',
            'name' => 'Əsas',
            'is_active' => true,
            'created_by' => $owner->id,
        ]);

        $document = Document::query()->create([
            'prefix_code' => 'ECP-MAIN/OPS-2026/0001',
            'title' => 'Admin versiya silmə',
            'category_id' => $category->id,
            'folder_id' => $folder->id,
            'created_by' => $owner->id,
        ]);

        $path = 'documents/2026/03/admin-version-delete.pdf';
        Storage::disk('local')->put($path, 'admin-version-content');

        $attachment = DocumentAttachment::query()->create([
            'document_id' => $document->id,
            'version_number' => 1,
            'version_note' => 'Admin siləcək',
            'original_name' => 'admin-version-delete.pdf',
            'stored_name' => 'admin-version-delete.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 21,
            'sha256' => hash('sha256', 'admin-version-content'),
            'disk' => 'local',
            'file_path' => $path,
            'uploaded_by' => $owner->id,
        ]);

        $this->actingAs($admin)
            ->delete(route('documents.versions.destroy', [$document, $attachment]))
            ->assertRedirect(route('documents.show', $document));

        $this->assertSoftDeleted('document_attachments', ['id' => $attachment->id]);
        Storage::disk('local')->assertMissing($path);
    }

    public function test_employee_cannot_update_or_delete_other_employee_document(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $this->grantDocumentPermissions($owner);
        $this->grantDocumentPermissions($other);

        $category = Category::query()->create([
            'code' => 'OPS',
            'name' => 'Əməliyyat',
            'is_active' => true,
            'created_by' => $owner->id,
        ]);

        $folder = Folder::query()->create([
            'code' => 'MAIN',
            'name' => 'Əsas',
            'is_active' => true,
            'created_by' => $owner->id,
        ]);

        $document = Document::query()->create([
            'prefix_code' => 'ECP-MAIN/OPS-2026/0001',
            'title' => 'Sahib sənədi',
            'category_id' => $category->id,
            'folder_id' => $folder->id,
            'created_by' => $owner->id,
        ]);

        $this->actingAs($other)->put(route('documents.update', $document), [
            'title' => 'Qadağan redaktə',
            'description' => null,
            'category_id' => $category->id,
            'folder_id' => $folder->id,
        ])->assertForbidden();

        $this->actingAs($other)->delete(route('documents.destroy', $document))->assertForbidden();
        $this->assertDatabaseHas('documents', ['id' => $document->id, 'deleted_at' => null]);
    }

    public function test_admin_can_delete_other_users_document(): void
    {
        $admin = User::factory()->create();
        $owner = User::factory()->create();

        Permission::query()->firstOrCreate(['name' => 'documents.view', 'guard_name' => 'web']);
        Role::query()->firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        $admin->assignRole('admin');
        $admin->givePermissionTo('documents.view');

        $category = Category::query()->create([
            'code' => 'OPS',
            'name' => 'Əməliyyat',
            'is_active' => true,
            'created_by' => $owner->id,
        ]);

        $folder = Folder::query()->create([
            'code' => 'MAIN',
            'name' => 'Əsas',
            'is_active' => true,
            'created_by' => $owner->id,
        ]);

        $document = Document::query()->create([
            'prefix_code' => 'ECP-MAIN/OPS-2026/0001',
            'title' => 'Admin siləcək',
            'category_id' => $category->id,
            'folder_id' => $folder->id,
            'created_by' => $owner->id,
        ]);

        $response = $this->actingAs($admin)->delete(route('documents.destroy', $document));
        $response->assertRedirect(route('documents.index'));

        $this->assertSoftDeleted('documents', ['id' => $document->id]);
    }

    public function test_download_is_blocked_when_attachment_checksum_is_invalid(): void
    {
        Storage::fake('local');

        $owner = User::factory()->create();
        $this->grantDocumentPermissions($owner);

        $category = Category::query()->create([
            'code' => 'OPS',
            'name' => 'Əməliyyat',
            'is_active' => true,
            'created_by' => $owner->id,
        ]);

        $folder = Folder::query()->create([
            'code' => 'MAIN',
            'name' => 'Əsas',
            'is_active' => true,
            'created_by' => $owner->id,
        ]);

        $document = Document::query()->create([
            'prefix_code' => 'ECP-MAIN/OPS-2026/0001',
            'title' => 'Checksum test sənədi',
            'category_id' => $category->id,
            'folder_id' => $folder->id,
            'created_by' => $owner->id,
        ]);

        Storage::disk('local')->put('documents/2026/03/checksum.pdf', 'original-content');

        $attachment = DocumentAttachment::query()->create([
            'document_id' => $document->id,
            'version_number' => 1,
            'version_note' => 'Checksum versiya',
            'original_name' => 'checksum.pdf',
            'stored_name' => 'checksum.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 16,
            'sha256' => hash('sha256', 'original-content'),
            'disk' => 'local',
            'file_path' => 'documents/2026/03/checksum.pdf',
            'uploaded_by' => $owner->id,
        ]);

        Storage::disk('local')->put('documents/2026/03/checksum.pdf', 'tampered-content');

        $this->actingAs($owner)
            ->get(route('documents.attachments.download', [$document, $attachment]))
            ->assertForbidden();
    }
}
