<?php

namespace Tests\Feature\Documents;

use App\Models\Document;
use App\Models\DocumentAudit;
use App\Models\DocumentShare;
use App\Models\DocumentVersion;
use App\Models\User;
use App\Models\UserDocumentQuota;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DocumentsExternalUrlTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        if (!Schema::hasColumn('documents', 'source_type')) {
            $migration = require database_path('migrations/2026_03_09_000001_add_external_source_fields_to_documents_table.php');
            $migration->up();
        }
    }

    public function test_external_url_documents_can_be_created_without_versions_or_quota_usage(): void
    {
        $user = $this->actingCurrentUser();

        $quota = UserDocumentQuota::updateOrCreate(
            ['user_id' => $user->id],
            [
                'quota_bytes' => 50 * 1024 * 1024,
                'used_bytes' => 4096,
                'warning_threshold_percent' => 80,
                'is_unlimited' => false,
            ]
        );

        $response = $this->postJson(route('documents.store'), [
            'source_type' => Document::SOURCE_EXTERNAL_URL,
            'title' => 'Junior English Syllabus',
            'external_url' => 'https://bw-syllabus.s3.us-east-1.amazonaws.com/cjss-subjects/english_syllabus.json',
            'description' => 'Shared from S3',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $document = Document::query()->latest('id')->firstOrFail();

        $this->assertSame(Document::SOURCE_EXTERNAL_URL, $document->source_type);
        $this->assertSame('https://bw-syllabus.s3.us-east-1.amazonaws.com/cjss-subjects/english_syllabus.json', $document->external_url);
        $this->assertNull($document->storage_path);
        $this->assertNull($document->checksum_sha256);
        $this->assertSame('json', $document->extension);
        $this->assertSame(0, DocumentVersion::where('document_id', $document->id)->count());
        $this->assertSame($quota->used_bytes, $quota->fresh()->used_bytes);
    }

    public function test_external_url_document_validation_requires_title_and_http_url(): void
    {
        $user = $this->actingCurrentUser();

        $response = $this->postJson(route('documents.store'), [
            'source_type' => Document::SOURCE_EXTERNAL_URL,
            'title' => '',
            'external_url' => 'ftp://example.com/file.pdf',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'external_url']);

        $this->assertSame(0, Document::where('owner_id', $user->id)->count());
    }

    public function test_external_url_preview_and_download_redirect_after_auditing(): void
    {
        $owner = $this->actingCurrentUser();
        $document = $this->createExternalDocument($owner);

        $previewResponse = $this->get(route('documents.preview', $document));
        $previewResponse->assertRedirect($document->external_url);

        $downloadResponse = $this->get(route('documents.download', $document));
        $downloadResponse->assertRedirect($document->external_url);

        $this->assertSame(
            1,
            DocumentAudit::where('document_id', $document->id)
                ->where('action', DocumentAudit::ACTION_PREVIEWED)
                ->count()
        );
        $this->assertSame(
            1,
            DocumentAudit::where('document_id', $document->id)
                ->where('action', DocumentAudit::ACTION_DOWNLOADED)
                ->count()
        );
        $this->assertSame(1, $document->fresh()->download_count);
    }

    public function test_public_links_redirect_for_external_url_documents(): void
    {
        $owner = User::factory()->create(['status' => 'Current']);
        $document = $this->createExternalDocument($owner, [
            'status' => Document::STATUS_PUBLISHED,
            'visibility' => Document::VISIBILITY_PUBLIC,
            'published_at' => now(),
        ]);

        $share = DocumentShare::create([
            'document_id' => $document->id,
            'shareable_type' => DocumentShare::TYPE_PUBLIC_LINK,
            'shareable_id' => null,
            'permission_level' => DocumentShare::PERMISSION_VIEW,
            'shared_by_user_id' => $owner->id,
            'access_token' => str_repeat('a', 64),
            'allow_download' => true,
            'view_count' => 0,
            'is_active' => true,
            'expires_at' => now()->addDay(),
        ]);

        $previewResponse = $this->get(route('documents.public.preview', ['token' => $share->access_token]));
        $previewResponse->assertRedirect($document->external_url);

        $downloadResponse = $this->get(route('documents.public.download', ['token' => $share->access_token]));
        $downloadResponse->assertRedirect($document->external_url);

        $this->assertSame(1, $document->fresh()->download_count);
    }

    public function test_external_url_documents_do_not_allow_version_upload_routes(): void
    {
        $owner = $this->actingCurrentUser();
        $document = $this->createExternalDocument($owner);

        $this->get(route('documents.versions.create', $document))
            ->assertForbidden();
    }

    private function actingCurrentUser(): User
    {
        $user = User::factory()->create(['status' => 'Current']);
        $this->actingAs($user);

        return $user;
    }

    private function createExternalDocument(User $owner, array $overrides = []): Document
    {
        return Document::create(array_merge([
            'title' => 'Remote Syllabus ' . now()->timestamp . '-' . random_int(1000, 9999),
            'source_type' => Document::SOURCE_EXTERNAL_URL,
            'external_url' => 'https://example.com/syllabus.pdf',
            'storage_disk' => null,
            'storage_path' => null,
            'original_name' => 'syllabus.pdf',
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'size_bytes' => null,
            'checksum_sha256' => null,
            'owner_id' => $owner->id,
            'status' => Document::STATUS_DRAFT,
            'visibility' => Document::VISIBILITY_PRIVATE,
            'current_version' => '1.0',
            'version_count' => 1,
        ], $overrides));
    }
}
