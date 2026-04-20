<?php

namespace Tests\Feature\Documents;

use App\Models\Document;
use App\Models\DocumentApproval;
use App\Models\DocumentFolder;
use App\Models\DocumentShare;
use App\Models\DocumentVersion;
use App\Models\User;
use App\Services\Documents\FolderService;
use App\Services\Documents\PublicLinkService;
use App\Services\Documents\SharingService;
use App\Services\Documents\WorkflowService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Tests\TestCase;

class DocumentsProductionHardeningTest extends TestCase {
    use DatabaseTransactions;

    protected function setUp(): void {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $this->withoutMiddleware(\Illuminate\Session\Middleware\AuthenticateSession::class);
    }

    public function test_internal_draft_is_not_visible_to_non_owner_but_published_internal_is_visible(): void {
        $owner = User::factory()->create(['status' => 'Current']);
        $viewer = User::factory()->create(['status' => 'Current']);

        $draftInternal = $this->createDocument($owner, [
            'status' => Document::STATUS_DRAFT,
            'visibility' => Document::VISIBILITY_INTERNAL,
        ]);

        $publishedInternal = $this->createDocument($owner, [
            'status' => Document::STATUS_PUBLISHED,
            'visibility' => Document::VISIBILITY_INTERNAL,
            'published_at' => now(),
        ]);

        $this->assertFalse($viewer->can('view', $draftInternal));
        $this->assertTrue($viewer->can('view', $publishedInternal));

        $visibleIds = Document::query()
            ->whereIn('id', [$draftInternal->id, $publishedInternal->id])
            ->visibleTo($viewer)
            ->pluck('id')
            ->all();

        $this->assertNotContains($draftInternal->id, $visibleIds);
        $this->assertContains($publishedInternal->id, $visibleIds);
    }

    public function test_public_link_resolution_fails_after_document_is_unpublished(): void {
        $owner = User::factory()->create(['status' => 'Current']);

        $document = $this->createDocument($owner, [
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
            'is_active' => true,
            'expires_at' => now()->addDay(),
        ]);

        /** @var PublicLinkService $publicLinkService */
        $publicLinkService = app(PublicLinkService::class);

        $this->assertNotNull($publicLinkService->resolveLink($share->access_token));

        $document->update([
            'status' => Document::STATUS_DRAFT,
            'published_at' => null,
        ]);

        $this->assertNull($publicLinkService->resolveLink($share->access_token));
    }

    public function test_review_is_blocked_once_document_leaves_review_state(): void {
        $owner = User::factory()->create(['status' => 'Current']);
        $reviewerOne = User::factory()->create(['status' => 'Current']);
        $reviewerTwo = User::factory()->create(['status' => 'Current']);

        $document = $this->createDocument($owner, [
            'status' => Document::STATUS_PENDING_REVIEW,
            'is_locked' => true,
            'locked_by_user_id' => $owner->id,
            'locked_at' => now(),
        ]);

        $version = DocumentVersion::create([
            'document_id' => $document->id,
            'version_number' => '1.0',
            'version_type' => DocumentVersion::TYPE_MAJOR,
            'storage_disk' => 'documents',
            'storage_path' => 'documents/test/version-1.pdf',
            'original_name' => 'version-1.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 1024,
            'checksum_sha256' => str_repeat('b', 64),
            'uploaded_by_user_id' => $owner->id,
            'is_current' => true,
            'created_at' => now(),
        ]);

        $document->update(['current_version' => $version->version_number]);

        DocumentApproval::create([
            'document_id' => $document->id,
            'version_id' => $version->id,
            'workflow_step' => 1,
            'reviewer_id' => $reviewerOne->id,
            'status' => DocumentApproval::STATUS_PENDING,
            'submitted_by_user_id' => $owner->id,
            'submitted_at' => now(),
        ]);

        DocumentApproval::create([
            'document_id' => $document->id,
            'version_id' => $version->id,
            'workflow_step' => 1,
            'reviewer_id' => $reviewerTwo->id,
            'status' => DocumentApproval::STATUS_PENDING,
            'submitted_by_user_id' => $owner->id,
            'submitted_at' => now(),
        ]);

        /** @var WorkflowService $workflowService */
        $workflowService = app(WorkflowService::class);

        $workflowService->reviewDocument($document, $reviewerOne, 'reject', 'Needs changes');

        $document->refresh();
        $this->assertSame(Document::STATUS_REVISION_REQUIRED, $document->status);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('no longer available for review');

        $workflowService->reviewDocument($document, $reviewerTwo, 'approve', 'Looks fine');
    }

    public function test_updating_existing_user_share_at_share_limit_is_allowed(): void {
        $owner = User::factory()->create(['status' => 'Current']);
        $document = $this->createDocument($owner);
        $targetUser = User::factory()->create(['status' => 'Current']);

        $existing = DocumentShare::create([
            'document_id' => $document->id,
            'shareable_type' => DocumentShare::TYPE_USER,
            'shareable_id' => (string) $targetUser->id,
            'permission_level' => DocumentShare::PERMISSION_VIEW,
            'shared_by_user_id' => $owner->id,
            'is_active' => true,
        ]);

        $additionalUsers = User::factory()->count(49)->create(['status' => 'Current']);
        foreach ($additionalUsers as $user) {
            DocumentShare::create([
                'document_id' => $document->id,
                'shareable_type' => DocumentShare::TYPE_USER,
                'shareable_id' => (string) $user->id,
                'permission_level' => DocumentShare::PERMISSION_VIEW,
                'shared_by_user_id' => $owner->id,
                'is_active' => true,
            ]);
        }

        /** @var SharingService $sharingService */
        $sharingService = app(SharingService::class);

        try {
            $updated = $sharingService->createShare($document, $owner, [
                'shareable_type' => DocumentShare::TYPE_USER,
                'shareable_id' => (string) $targetUser->id,
                'permission' => DocumentShare::PERMISSION_MANAGE,
            ]);
        } catch (ValidationException $e) {
            $this->fail('Existing share update should not be blocked at share cap: ' . $e->getMessage());
        }

        $this->assertSame($existing->id, $updated->id);
        $this->assertSame(DocumentShare::PERMISSION_MANAGE, $updated->permission_level);
        $this->assertSame(
            50,
            DocumentShare::where('document_id', $document->id)
                ->where('shareable_type', DocumentShare::TYPE_USER)
                ->where('is_active', true)
                ->whereNull('revoked_at')
                ->count()
        );
    }

    public function test_moving_unfiled_documents_into_folder_does_not_fail_count_updates(): void {
        $owner = User::factory()->create(['status' => 'Current']);

        /** @var FolderService $folderService */
        $folderService = app(FolderService::class);

        $targetFolder = DocumentFolder::create([
            'name' => 'Target Folder',
            'owner_id' => $owner->id,
            'repository_type' => DocumentFolder::REPOSITORY_PERSONAL,
            'visibility' => DocumentFolder::VISIBILITY_PRIVATE,
            'depth' => 0,
            'document_count' => 0,
        ]);
        $folderService->updatePathAndDepth($targetFolder);

        $document = $this->createDocument($owner, [
            'folder_id' => null,
        ]);

        $this->actingAs($owner)
            ->postJson(route('documents.folders.move'), [
                'type' => 'document',
                'ids' => [$document->id],
                'target_folder_id' => $targetFolder->id,
            ])
            ->assertOk()
            ->assertJson([
                'success' => true,
                'moved' => 1,
                'unauthorized' => 0,
            ]);

        $this->assertSame($targetFolder->id, $document->fresh()->folder_id);
        $this->assertSame(1, $targetFolder->fresh()->document_count);
    }

    private function createDocument(User $owner, array $overrides = []): Document {
        return Document::create(array_merge([
            'title' => 'Test Document ' . now()->timestamp . '-' . random_int(1000, 9999),
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
