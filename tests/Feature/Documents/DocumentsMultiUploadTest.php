<?php

namespace Tests\Feature\Documents;

use App\Models\Document;
use App\Models\DocumentFolder;
use App\Models\DocumentVersion;
use App\Models\User;
use App\Models\UserDocumentQuota;
use App\Services\Documents\FolderService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentsMultiUploadTest extends TestCase {
    use DatabaseTransactions;

    protected function setUp(): void {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
    }

    public function test_sequential_uploads_create_multiple_documents_and_versions(): void {
        Storage::fake('documents');
        $user = $this->actingCurrentUser();

        $firstUpload = $this->postJson(route('documents.store'), [
            'file' => UploadedFile::fake()->create('policy-one.pdf', 10, 'application/pdf'),
            'title' => 'Staff Policy Pack',
            'description' => 'Batch upload metadata',
        ]);

        $firstUpload->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $secondUpload = $this->postJson(route('documents.store'), [
            'file' => UploadedFile::fake()->create('policy-two.pdf', 10, 'application/pdf'),
            'title' => 'Staff Policy Pack',
            'description' => 'Batch upload metadata',
        ]);

        $secondUpload->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $documents = Document::where('owner_id', $user->id)->get();

        $this->assertCount(2, $documents);
        $this->assertSame(2, $documents->where('title', 'Staff Policy Pack')->count());
        $this->assertCount(
            2,
            DocumentVersion::whereIn('document_id', $documents->pluck('id'))->get()
        );
    }

    public function test_partial_success_when_quota_blocks_later_file(): void {
        Storage::fake('documents');
        $user = $this->actingCurrentUser();

        UserDocumentQuota::updateOrCreate(
            ['user_id' => $user->id],
            [
                'quota_bytes' => 1500,
                'used_bytes' => 0,
                'warning_threshold_percent' => 80,
                'is_unlimited' => false,
            ]
        );

        $firstUpload = $this->postJson(route('documents.store'), [
            'file' => UploadedFile::fake()->create('first.pdf', 1, 'application/pdf'),
            'title' => 'Quota Test',
        ]);

        $firstUpload->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $secondUpload = $this->postJson(route('documents.store'), [
            'file' => UploadedFile::fake()->create('second.pdf', 1, 'application/pdf'),
            'title' => 'Quota Test',
        ]);

        $secondUpload->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);

        $this->assertStringContainsString(
            'Storage quota exceeded',
            (string) $secondUpload->json('message')
        );
        $this->assertSame(1, Document::where('owner_id', $user->id)->count());
        $this->assertSame(
            1,
            DocumentVersion::whereIn(
                'document_id',
                Document::where('owner_id', $user->id)->pluck('id')
            )->count()
        );
    }

    public function test_missing_title_falls_back_to_filename_without_extension(): void {
        Storage::fake('documents');
        $user = $this->actingCurrentUser();

        $response = $this->postJson(route('documents.store'), [
            'file' => UploadedFile::fake()->create('school-calendar-2026.pdf', 5, 'application/pdf'),
            'description' => 'No title supplied',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $document = Document::where('owner_id', $user->id)->latest('id')->first();
        $this->assertNotNull($document);
        $this->assertSame('school-calendar-2026', $document->title);
    }

    public function test_preserve_original_name_keeps_filename_even_when_title_is_supplied(): void {
        Storage::fake('documents');
        $user = $this->actingCurrentUser();

        $response = $this->postJson(route('documents.store'), [
            'file' => UploadedFile::fake()->create('term-one-report.pdf', 5, 'application/pdf'),
            'title' => 'Custom Renamed Title',
            'preserve_original_name' => true,
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $document = Document::where('owner_id', $user->id)->latest('id')->first();
        $this->assertNotNull($document);
        $this->assertSame('term-one-report', $document->title);
        $this->assertSame('term-one-report.pdf', $document->original_name);
    }

    public function test_uploading_to_a_folder_persists_folder_assignment_and_updates_counts(): void {
        Storage::fake('documents');
        $user = $this->actingCurrentUser();
        $folder = $this->createFolder($user, [
            'name' => 'Policies',
            'document_count' => 0,
        ]);

        $response = $this->postJson(route('documents.store'), [
            'file' => UploadedFile::fake()->create('policies.pdf', 5, 'application/pdf'),
            'title' => 'Policies',
            'folder_id' => $folder->id,
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $document = Document::where('owner_id', $user->id)->latest('id')->first();

        $this->assertNotNull($document);
        $this->assertSame($folder->id, $document->folder_id);
        $this->assertSame(1, $folder->fresh()->document_count);
    }

    public function test_upload_cannot_target_another_users_private_folder(): void {
        Storage::fake('documents');
        $user = $this->actingCurrentUser();
        $otherOwner = User::factory()->create(['status' => 'Current']);
        $folder = $this->createFolder($otherOwner, [
            'name' => 'Private Owner Folder',
            'document_count' => 0,
        ]);

        $response = $this->postJson(route('documents.store'), [
            'file' => UploadedFile::fake()->create('policies.pdf', 5, 'application/pdf'),
            'title' => 'Unauthorized Folder Upload',
            'folder_id' => $folder->id,
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
            ]);

        $this->assertSame(0, Document::where('owner_id', $user->id)->count());
        $this->assertSame(0, $folder->fresh()->document_count);
    }

    public function test_invalid_file_returns_json_validation_error_payload(): void {
        Storage::fake('documents');
        $this->actingCurrentUser();

        $response = $this->postJson(route('documents.store'), [
            'file' => UploadedFile::fake()->create('malware.exe', 10, 'application/octet-stream'),
            'title' => 'Unsafe Binary',
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('message', 'The given data was invalid.');
        $response->assertJsonValidationErrors(['file']);
    }

    private function actingCurrentUser(): User {
        $user = User::factory()->create(['status' => 'Current']);
        $this->actingAs($user);

        return $user;
    }

    private function createFolder(User $owner, array $overrides = []): DocumentFolder {
        /** @var FolderService $folderService */
        $folderService = app(FolderService::class);

        $folder = DocumentFolder::create(array_merge([
            'name' => 'Folder ' . now()->timestamp . '-' . random_int(1000, 9999),
            'owner_id' => $owner->id,
            'repository_type' => DocumentFolder::REPOSITORY_PERSONAL,
            'visibility' => DocumentFolder::VISIBILITY_PRIVATE,
            'depth' => 0,
        ], $overrides));

        $folderService->updatePathAndDepth($folder);

        return $folder->fresh();
    }
}
