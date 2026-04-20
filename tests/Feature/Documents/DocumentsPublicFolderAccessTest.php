<?php

namespace Tests\Feature\Documents;

use App\Models\Document;
use App\Models\DocumentFolder;
use App\Models\Role;
use App\Models\User;
use App\Services\Documents\FolderService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class DocumentsPublicFolderAccessTest extends TestCase {
    use DatabaseTransactions;

    protected function setUp(): void {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $this->withoutMiddleware(\Illuminate\Session\Middleware\AuthenticateSession::class);
    }

    public function test_owner_can_create_public_root_folder_visible_to_other_staff(): void {
        $owner = $this->actingCurrentUser();

        $this->postJson(route('documents.folders.store'), [
            'name' => 'Team Public Space',
            'access_scope' => 'public',
        ])->assertOk()->assertJson([
            'success' => true,
        ]);

        $folder = DocumentFolder::where('owner_id', $owner->id)
            ->where('name', 'Team Public Space')
            ->firstOrFail();

        $this->assertSame(DocumentFolder::REPOSITORY_PERSONAL, $folder->repository_type);
        $this->assertSame(DocumentFolder::VISIBILITY_INTERNAL, $folder->visibility);

        $viewer = User::factory()->create(['status' => 'Current']);

        $treeResponse = $this->actingAs($viewer)->getJson(route('documents.folders.tree'))->assertOk();
        $this->assertContains($folder->id, $this->treeGroupIds($treeResponse, 'public'));

        $this->actingAs($viewer)
            ->get(route('documents.index'))
            ->assertOk()
            ->assertSee('Team Public Space');
    }

    public function test_private_personal_folder_remains_hidden_from_other_users(): void {
        $owner = User::factory()->create(['status' => 'Current']);
        $privateFolder = $this->createFolder($owner, [
            'name' => 'Owner Secret Folder',
            'visibility' => DocumentFolder::VISIBILITY_PRIVATE,
        ]);

        $viewer = User::factory()->create(['status' => 'Current']);
        $response = $this->actingAs($viewer)->getJson(route('documents.folders.tree'))->assertOk();

        $visibleIds = array_merge(
            $this->treeGroupIds($response, 'personal'),
            $this->treeGroupIds($response, 'public'),
            $this->treeGroupIds($response, 'institutional'),
            $this->treeGroupIds($response, 'shared'),
            $this->treeGroupIds($response, 'department'),
        );

        $this->assertNotContains($privateFolder->id, $visibleIds);

        $this->actingAs($viewer)
            ->get(route('documents.index'))
            ->assertOk()
            ->assertDontSee('Owner Secret Folder');
    }

    public function test_admin_views_personal_roots_with_owner_context(): void {
        $admin = User::factory()->create(['status' => 'Current']);
        $this->makeDocumentsAdmin($admin);

        $owner = User::factory()->create(['status' => 'Current']);
        $ownerRoot = DocumentFolder::where('owner_id', $owner->id)
            ->where('name', 'My Documents')
            ->whereNull('parent_id')
            ->where('repository_type', DocumentFolder::REPOSITORY_PERSONAL)
            ->firstOrFail();

        // Private personal folders from other users should NOT appear in the
        // admin's personal tree — they were previously shown as duplicates.
        $treeResponse = $this->actingAs($admin)->getJson(route('documents.folders.tree'))->assertOk();
        $ownerNode = $this->findTreeNodeById((array) $treeResponse->json('tree.personal', []), $ownerRoot->id);

        $this->assertNull($ownerNode, 'Other users\' private personal folders must not appear in admin personal tree');

        // Admin's own personal folder should still be present.
        $adminRoot = DocumentFolder::where('owner_id', $admin->id)
            ->where('name', 'My Documents')
            ->whereNull('parent_id')
            ->where('repository_type', DocumentFolder::REPOSITORY_PERSONAL)
            ->firstOrFail();

        $adminNode = $this->findTreeNodeById((array) $treeResponse->json('tree.personal', []), $adminRoot->id);
        $this->assertNotNull($adminNode, 'Admin\'s own personal folder must appear in personal tree');

        // When the other user makes their folder public, it should appear in the public group.
        $ownerRoot->update(['visibility' => DocumentFolder::VISIBILITY_INTERNAL]);

        $treeResponse = $this->actingAs($admin)->getJson(route('documents.folders.tree'))->assertOk();
        $publicNode = $this->findTreeNodeById((array) $treeResponse->json('tree.public', []), $ownerRoot->id);

        $this->assertNotNull($publicNode, 'Other users\' internal personal folders should appear in public tree');
        $this->assertSame($owner->full_name, $publicNode['owner_name'] ?? null);
    }

    public function test_toggling_folder_to_public_cascades_to_descendants(): void {
        $owner = User::factory()->create(['status' => 'Current']);
        $root = $this->createFolder($owner, [
            'name' => 'Cascade Root',
            'visibility' => DocumentFolder::VISIBILITY_PRIVATE,
        ]);
        $child = $this->createFolder($owner, [
            'name' => 'Cascade Child',
            'parent_id' => $root->id,
            'visibility' => DocumentFolder::VISIBILITY_PRIVATE,
        ]);

        $this->actingAs($owner)
            ->patchJson('/documents/folders/' . $root->id . '/access', [
                'access_scope' => 'public',
            ])->assertOk()->assertJson([
                'success' => true,
                'visibility' => DocumentFolder::VISIBILITY_INTERNAL,
            ]);

        $this->assertSame(DocumentFolder::VISIBILITY_INTERNAL, $root->fresh()->visibility);
        $this->assertSame(DocumentFolder::VISIBILITY_INTERNAL, $child->fresh()->visibility);
    }

    public function test_toggling_folder_back_to_private_cascades_to_descendants(): void {
        $owner = User::factory()->create(['status' => 'Current']);
        $root = $this->createFolder($owner, [
            'name' => 'Public Root',
            'visibility' => DocumentFolder::VISIBILITY_INTERNAL,
        ]);
        $child = $this->createFolder($owner, [
            'name' => 'Public Child',
            'parent_id' => $root->id,
            'visibility' => DocumentFolder::VISIBILITY_INTERNAL,
        ]);

        $this->actingAs($owner)
            ->patchJson('/documents/folders/' . $root->id . '/access', [
                'access_scope' => 'private',
            ])->assertOk()->assertJson([
                'success' => true,
                'visibility' => DocumentFolder::VISIBILITY_PRIVATE,
            ]);

        $this->assertSame(DocumentFolder::VISIBILITY_PRIVATE, $root->fresh()->visibility);
        $this->assertSame(DocumentFolder::VISIBILITY_PRIVATE, $child->fresh()->visibility);
    }

    public function test_non_owner_cannot_toggle_access_scope_for_someone_else_folder(): void {
        $owner = User::factory()->create(['status' => 'Current']);
        $folder = $this->createFolder($owner, [
            'name' => 'Owner Folder',
            'visibility' => DocumentFolder::VISIBILITY_PRIVATE,
        ]);

        $viewer = User::factory()->create(['status' => 'Current']);
        $this->actingAs($viewer)
            ->patchJson('/documents/folders/' . $folder->id . '/access', [
                'access_scope' => 'public',
            ])->assertStatus(403);
    }

    public function test_non_owner_cannot_create_subfolder_inside_another_users_public_folder(): void {
        $owner = User::factory()->create(['status' => 'Current']);
        $publicParent = $this->createFolder($owner, [
            'name' => 'Public Parent',
            'visibility' => DocumentFolder::VISIBILITY_INTERNAL,
        ]);

        $viewer = User::factory()->create(['status' => 'Current']);
        $this->actingAs($viewer)
            ->postJson(route('documents.folders.store'), [
                'name' => 'Should Fail',
                'parent_id' => $publicParent->id,
            ])->assertStatus(403);
    }

    public function test_subfolder_under_public_parent_inherits_parent_visibility_even_with_private_payload(): void {
        $owner = User::factory()->create(['status' => 'Current']);
        $publicParent = $this->createFolder($owner, [
            'name' => 'Public Parent Inherit',
            'visibility' => DocumentFolder::VISIBILITY_INTERNAL,
            'repository_type' => DocumentFolder::REPOSITORY_PERSONAL,
        ]);

        $this->actingAs($owner)
            ->postJson(route('documents.folders.store'), [
                'name' => 'Inherited Child',
                'parent_id' => $publicParent->id,
                'repository_type' => DocumentFolder::REPOSITORY_PERSONAL,
                'access_scope' => 'private',
            ])->assertOk()->assertJson([
                'success' => true,
            ]);

        $child = DocumentFolder::where('name', 'Inherited Child')->firstOrFail();
        $this->assertSame(DocumentFolder::VISIBILITY_INTERNAL, $child->visibility);
        $this->assertSame($publicParent->repository_type, $child->repository_type);
    }

    public function test_moving_subtree_under_public_parent_inherits_visibility_recursively(): void {
        $owner = User::factory()->create(['status' => 'Current']);

        $targetPublic = $this->createFolder($owner, [
            'name' => 'Target Public',
            'visibility' => DocumentFolder::VISIBILITY_INTERNAL,
            'repository_type' => DocumentFolder::REPOSITORY_PERSONAL,
        ]);

        $movingRoot = $this->createFolder($owner, [
            'name' => 'Moving Root',
            'visibility' => DocumentFolder::VISIBILITY_PRIVATE,
            'repository_type' => DocumentFolder::REPOSITORY_PERSONAL,
        ]);

        $movingChild = $this->createFolder($owner, [
            'name' => 'Moving Child',
            'parent_id' => $movingRoot->id,
            'visibility' => DocumentFolder::VISIBILITY_PRIVATE,
            'repository_type' => DocumentFolder::REPOSITORY_PERSONAL,
        ]);

        $this->actingAs($owner)
            ->postJson(route('documents.folders.move'), [
                'type' => 'folder',
                'ids' => [$movingRoot->id],
                'target_folder_id' => $targetPublic->id,
            ])->assertOk()->assertJson([
                'success' => true,
            ]);

        $this->assertSame(DocumentFolder::VISIBILITY_INTERNAL, $movingRoot->fresh()->visibility);
        $this->assertSame(DocumentFolder::VISIBILITY_INTERNAL, $movingChild->fresh()->visibility);
        $this->assertSame($targetPublic->repository_type, $movingRoot->fresh()->repository_type);
        $this->assertSame($targetPublic->repository_type, $movingChild->fresh()->repository_type);
    }

    public function test_documents_inside_public_folder_still_follow_document_visibility_rules(): void {
        $owner = User::factory()->create(['status' => 'Current']);
        $publicFolder = $this->createFolder($owner, [
            'name' => 'Folder With Private Doc',
            'visibility' => DocumentFolder::VISIBILITY_INTERNAL,
        ]);

        $document = Document::create([
            'title' => 'Owner Private Draft',
            'storage_disk' => 'documents',
            'storage_path' => 'documents/test/private-draft.pdf',
            'original_name' => 'private-draft.pdf',
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'size_bytes' => 1024,
            'checksum_sha256' => str_repeat('d', 64),
            'folder_id' => $publicFolder->id,
            'owner_id' => $owner->id,
            'status' => Document::STATUS_DRAFT,
            'visibility' => Document::VISIBILITY_PRIVATE,
            'current_version' => '1.0',
            'version_count' => 1,
        ]);

        $viewer = User::factory()->create(['status' => 'Current']);
        $this->actingAs($viewer)
            ->get(route('documents.index', ['folder' => $publicFolder->id]))
            ->assertOk()
            ->assertDontSee($document->title);
    }

    public function test_non_current_user_cannot_create_or_list_folders(): void {
        $formerUser = User::factory()->create(['status' => 'Former']);

        $this->actingAs($formerUser)
            ->postJson(route('documents.folders.store'), [
                'name' => 'Should Not Be Created',
            ])->assertStatus(403);

        $this->actingAs($formerUser)
            ->getJson(route('documents.folders.tree'))
            ->assertStatus(403);
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

    private function makeDocumentsAdmin(User $user): void {
        $role = Role::query()->firstOrCreate(
            ['name' => 'Administrator'],
            ['description' => 'Administrator']
        );

        $user->roles()->syncWithoutDetaching([$role->id]);
    }

    /**
     * @return array<int>
     */
    private function treeGroupIds(TestResponse $response, string $group): array {
        return $this->flattenTreeIds((array) $response->json("tree.$group", []));
    }

    /**
     * @param array<int, array<string, mixed>> $nodes
     * @return array<string, mixed>|null
     */
    private function findTreeNodeById(array $nodes, int $targetId): ?array {
        foreach ($nodes as $node) {
            if ((int) ($node['id'] ?? 0) === $targetId) {
                return $node;
            }

            if (!empty($node['children']) && is_array($node['children'])) {
                $childMatch = $this->findTreeNodeById($node['children'], $targetId);

                if ($childMatch !== null) {
                    return $childMatch;
                }
            }
        }

        return null;
    }

    /**
     * @param array<int, array<string, mixed>> $nodes
     * @return array<int>
     */
    private function flattenTreeIds(array $nodes): array {
        $ids = [];

        foreach ($nodes as $node) {
            if (isset($node['id'])) {
                $ids[] = (int) $node['id'];
            }

            if (!empty($node['children']) && is_array($node['children'])) {
                $ids = array_merge($ids, $this->flattenTreeIds($node['children']));
            }
        }

        return $ids;
    }
}
