<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        if (!Schema::hasTable('document_shares') || !Schema::hasTable('document_folder_permissions')) {
            return;
        }

        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE `document_shares` MODIFY `shareable_id` VARCHAR(191) NULL');
            DB::statement('ALTER TABLE `document_folder_permissions` MODIFY `permissionable_id` VARCHAR(191) NOT NULL');
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE document_shares ALTER COLUMN shareable_id TYPE VARCHAR(191) USING shareable_id::varchar');
            DB::statement('ALTER TABLE document_folder_permissions ALTER COLUMN permissionable_id TYPE VARCHAR(191) USING permissionable_id::varchar');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        if (!Schema::hasTable('document_shares') || !Schema::hasTable('document_folder_permissions')) {
            return;
        }

        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE `document_shares` MODIFY `shareable_id` BIGINT UNSIGNED NULL');
            DB::statement('ALTER TABLE `document_folder_permissions` MODIFY `permissionable_id` BIGINT UNSIGNED NOT NULL');
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE document_shares ALTER COLUMN shareable_id TYPE BIGINT USING NULLIF(shareable_id, \'\')::bigint');
            DB::statement('ALTER TABLE document_folder_permissions ALTER COLUMN permissionable_id TYPE BIGINT USING permissionable_id::bigint');
        }
    }
};
