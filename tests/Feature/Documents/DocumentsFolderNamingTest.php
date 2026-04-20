<?php

namespace Tests\Feature\Documents;

use App\Models\DocumentFolder;
use App\Models\User;
use App\Services\Documents\FolderService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DocumentsFolderNamingTest extends TestCase {
    use DatabaseTransactions;

    protected function setUp(): void {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
    }

    public function test_different_users_can_create_same_root_personal_folder_name(): void {
        $firstUser = User::factory()->create(['status' => 'Current']);
        $secondUser = User::factory()->create(['status' => 'Current']);

        $existingFolder = DocumentFolder::create([
            'name' => 'Home',
            'owner_id' => $firstUser->id,
            'repository_type' => DocumentFolder::REPOSITORY_PERSONAL,
            'visibility' => DocumentFolder::VISIBILITY_PRIVATE,
            'depth' => 0,
        ]);
        $existingFolder->update(['path' => '/' . $existingFolder->id]);

        $this->actingAs($secondUser)
            ->postJson(route('documents.folders.store'), [
                'name' => 'Home',
            ])->assertOk()->assertJson(['success' => true]);

        $this->assertSame(
            2,
            DocumentFolder::where('name', 'Home')
                ->where('repository_type', DocumentFolder::REPOSITORY_PERSONAL)
                ->whereNull('parent_id')
                ->whereIn('owner_id', [$firstUser->id, $secondUser->id])
                ->count()
        );

        $this->assertSame(1, DocumentFolder::where('owner_id', $firstUser->id)->where('name', 'Home')->count());
        $this->assertSame(1, DocumentFolder::where('owner_id', $secondUser->id)->where('name', 'Home')->count());
    }

    public function test_same_user_cannot_create_duplicate_root_folder_name_in_same_repository(): void {
        $user = User::factory()->create(['status' => 'Current']);

        $this->actingAs($user)
            ->postJson(route('documents.folders.store'), [
                'name' => 'Home',
            ])->assertOk()->assertJson(['success' => true]);

        $this->actingAs($user)
            ->from('/documents')
            ->post(route('documents.folders.store'), [
                'name' => 'Home',
            ])->assertStatus(302)->assertSessionHasErrors(['name']);

        $this->assertSame(
            1,
            DocumentFolder::where('owner_id', $user->id)
                ->where('name', 'Home')
                ->whereNull('parent_id')
                ->where('repository_type', DocumentFolder::REPOSITORY_PERSONAL)
                ->count()
        );
    }

    public function test_personal_root_provisioning_is_idempotent_for_current_user(): void {
        $user = User::factory()->create(['status' => 'Current']);

        /** @var FolderService $folderService */
        $folderService = app(FolderService::class);
        $folderService->ensurePersonalRootFolder($user);
        $folderService->ensurePersonalRootFolder($user);

        $rootFolders = DocumentFolder::where('owner_id', $user->id)
            ->where('name', 'My Documents')
            ->whereNull('parent_id')
            ->where('repository_type', DocumentFolder::REPOSITORY_PERSONAL)
            ->get();

        $this->assertCount(1, $rootFolders);
        $this->assertSame('/' . $rootFolders->first()->id, $rootFolders->first()->path);
    }

    public function test_changing_user_to_current_auto_provisions_personal_root_folder(): void {
        $user = User::factory()->create(['status' => 'Former']);

        $this->assertSame(
            0,
            DocumentFolder::where('owner_id', $user->id)
                ->where('name', 'My Documents')
                ->whereNull('parent_id')
                ->where('repository_type', DocumentFolder::REPOSITORY_PERSONAL)
                ->count()
        );

        $user->update(['status' => 'Current']);

        $rootFolder = DocumentFolder::where('owner_id', $user->id)
            ->where('name', 'My Documents')
            ->whereNull('parent_id')
            ->where('repository_type', DocumentFolder::REPOSITORY_PERSONAL)
            ->first();

        $this->assertNotNull($rootFolder);
        $this->assertSame('/' . $rootFolder->id, $rootFolder->path);
    }
}
