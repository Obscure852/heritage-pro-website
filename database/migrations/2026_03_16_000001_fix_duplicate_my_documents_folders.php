<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // Step 1: Find all users with duplicate "My Documents" root folders
        $duplicates = DB::table('document_folders')
            ->select('owner_id')
            ->where('name', 'My Documents')
            ->where('repository_type', 'personal')
            ->whereNull('parent_id')
            ->whereNull('deleted_at')
            ->groupBy('owner_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('owner_id');

        foreach ($duplicates as $ownerId) {
            // Keep the oldest folder (lowest id) for each user
            $keepId = DB::table('document_folders')
                ->where('owner_id', $ownerId)
                ->where('name', 'My Documents')
                ->where('repository_type', 'personal')
                ->whereNull('parent_id')
                ->whereNull('deleted_at')
                ->orderBy('id')
                ->value('id');

            $duplicateIds = DB::table('document_folders')
                ->where('owner_id', $ownerId)
                ->where('name', 'My Documents')
                ->where('repository_type', 'personal')
                ->whereNull('parent_id')
                ->whereNull('deleted_at')
                ->where('id', '!=', $keepId)
                ->pluck('id');

            if ($duplicateIds->isEmpty()) {
                continue;
            }

            // Reassign any documents from duplicate folders to the kept folder
            DB::table('documents')
                ->whereIn('folder_id', $duplicateIds)
                ->update(['folder_id' => $keepId]);

            // Reassign any child folders from duplicates to the kept folder
            DB::table('document_folders')
                ->whereIn('parent_id', $duplicateIds)
                ->update(['parent_id' => $keepId]);

            // Soft-delete the duplicate folders
            DB::table('document_folders')
                ->whereIn('id', $duplicateIds)
                ->update(['deleted_at' => now()]);
        }

        // Step 2: Add unique index for non-root folders (parent_id is NOT NULL)
        // Note: MySQL treats NULLs as distinct in unique indexes, so this constraint
        // only prevents duplicates for sub-folders. Root-level duplicates (parent_id = NULL)
        // are prevented by the app-level firstOrCreate in UserObserver.
        Schema::table('document_folders', function (Blueprint $table) {
            $table->unique(
                ['owner_id', 'name', 'parent_id', 'repository_type'],
                'document_folders_unique_per_owner'
            );
        });
    }

    public function down(): void {
        Schema::table('document_folders', function (Blueprint $table) {
            $table->dropUnique('document_folders_unique_per_owner');
        });
    }
};
