<?php

namespace Tests\Feature\Documents;

use App\Models\DocumentFolder;
use App\Models\User;
use App\Services\Documents\FolderService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DocumentsFolderBulkDeleteTest extends TestCase {
    use DatabaseTransactions;

    protected function setUp(): void {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
    }

    public function test_bulk_delete_moves_selected_root_folder_to_trash(): void {
        $user = $this->actingCurrentUser();
        $folder = $this->createFolder($user, [
            'name' => 'Root Folder',
        ]);

        $this->postJson(route('documents.folders.bulk.destroy'), [
            'ids' => [$folder->id],
        ])->assertOk()
            ->assertJson([
                'success' => true,
                'deleted' => 1,
                'unauthorized' => 0,
            ]);

        $this->assertSoftDeleted('document_folders', [
            'id' => $folder->id,
        ]);
    }

    public function test_bulk_delete_skips_nested_folder_when_parent_is_also_selected(): void {
        $user = $this->actingCurrentUser();
        $parent = $this->createFolder($user, [
            'name' => 'Parent Folder',
        ]);
        $child = $this->createFolder($user, [
            'name' => 'Child Folder',
            'parent_id' => $parent->id,
        ]);

        $this->postJson(route('documents.folders.bulk.destroy'), [
            'ids' => [$parent->id, $child->id],
        ])->assertOk()
            ->assertJson([
                'success' => true,
                'deleted' => 1,
                'unauthorized' => 0,
            ]);

        $this->assertSoftDeleted('document_folders', [
            'id' => $parent->id,
        ]);
        $this->assertSoftDeleted('document_folders', [
            'id' => $child->id,
        ]);
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
