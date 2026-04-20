<?php

namespace Tests\Feature\Documents;

use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\User;
use App\Models\UserDocumentQuota;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentsVersionHardeningTest extends TestCase {
    use DatabaseTransactions;

    protected function setUp(): void {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
    }

    public function test_version_upload_rejects_disallowed_extension(): void {
        Storage::fake('documents');
        $owner = User::factory()->create(['status' => 'Current']);
        $document = $this->createDocument($owner);

        $response = $this->actingAs($owner)
            ->from(route('documents.versions.create', $document))
            ->post(route('documents.versions.store', $document), [
                'file' => UploadedFile::fake()->create('payload.exe', 8, 'application/octet-stream'),
                'version_type' => DocumentVersion::TYPE_MINOR,
                'version_notes' => 'Attempted invalid extension',
            ]);

        $response->assertStatus(302)
            ->assertSessionHasErrors(['file']);

        $this->assertSame(0, DocumentVersion::where('document_id', $document->id)->count());
    }

    public function test_version_upload_blocks_when_quota_projection_exceeds_hard_limit(): void {
        Storage::fake('documents');
        $owner = User::factory()->create(['status' => 'Current']);
        $document = $this->createDocument($owner, ['size_bytes' => 1000]);

        UserDocumentQuota::updateOrCreate(
            ['user_id' => $owner->id],
            [
                'quota_bytes' => 1500,
                'used_bytes' => 1400,
                'warning_threshold_percent' => 80,
                'is_unlimited' => false,
            ]
        );

        $response = $this->actingAs($owner)
            ->from(route('documents.versions.create', $document))
            ->post(route('documents.versions.store', $document), [
                'file' => UploadedFile::fake()->create('replacement.pdf', 2, 'application/pdf'),
                'version_type' => DocumentVersion::TYPE_MAJOR,
            ]);

        $response->assertStatus(302)
            ->assertSessionHasErrors(['file']);

        $this->assertStringContainsString(
            'Storage quota exceeded',
            (string) session('errors')->first('file')
        );
        $this->assertSame(0, DocumentVersion::where('document_id', $document->id)->count());
    }

    public function test_version_restore_blocks_when_restored_file_exceeds_quota_projection(): void {
        Storage::fake('documents');
        $owner = User::factory()->create(['status' => 'Current']);
        $document = $this->createDocument($owner, ['size_bytes' => 1000]);

        $currentVersion = DocumentVersion::create([
            'document_id' => $document->id,
            'version_number' => '1.0',
            'version_type' => DocumentVersion::TYPE_MAJOR,
            'storage_disk' => 'documents',
            'storage_path' => 'user-' . $owner->id . '/current.pdf',
            'original_name' => 'current.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 1000,
            'checksum_sha256' => str_repeat('a', 64),
            'uploaded_by_user_id' => $owner->id,
            'is_current' => true,
        ]);

        $restoreSource = DocumentVersion::create([
            'document_id' => $document->id,
            'version_number' => '0.9',
            'version_type' => DocumentVersion::TYPE_MINOR,
            'storage_disk' => 'documents',
            'storage_path' => 'user-' . $owner->id . '/restore-source.pdf',
            'original_name' => 'restore-source.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 5000,
            'checksum_sha256' => str_repeat('b', 64),
            'uploaded_by_user_id' => $owner->id,
            'is_current' => false,
        ]);

        $document->update(['current_version' => $currentVersion->version_number]);

        UserDocumentQuota::updateOrCreate(
            ['user_id' => $owner->id],
            [
                'quota_bytes' => 1500,
                'used_bytes' => 1400,
                'warning_threshold_percent' => 80,
                'is_unlimited' => false,
            ]
        );

        $response = $this->actingAs($owner)
            ->from(route('documents.show', $document))
            ->post(route('documents.versions.restore', [$document, $restoreSource]));

        $response->assertStatus(302)
            ->assertSessionHasErrors(['file']);

        $this->assertStringContainsString(
            'Storage quota exceeded',
            (string) session('errors')->first('file')
        );
        $this->assertSame(2, DocumentVersion::where('document_id', $document->id)->count());
    }

    private function createDocument(User $owner, array $overrides = []): Document {
        return Document::create(array_merge([
            'title' => 'Version Hardening Document ' . now()->timestamp . '-' . random_int(1000, 9999),
            'storage_disk' => 'documents',
            'storage_path' => 'documents/test/document.pdf',
            'original_name' => 'document.pdf',
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'size_bytes' => 2048,
            'checksum_sha256' => str_repeat('c', 64),
            'owner_id' => $owner->id,
            'status' => Document::STATUS_DRAFT,
            'visibility' => Document::VISIBILITY_PRIVATE,
            'current_version' => '1.0',
            'version_count' => 1,
        ], $overrides));
    }
}

