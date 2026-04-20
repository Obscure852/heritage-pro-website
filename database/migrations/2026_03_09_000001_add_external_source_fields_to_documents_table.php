<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('documents')) {
            return;
        }

        Schema::table('documents', function (Blueprint $table) {
            if (!Schema::hasColumn('documents', 'source_type')) {
                $table->string('source_type', 30)->default('upload')->after('description');
            }

            if (!Schema::hasColumn('documents', 'external_url')) {
                $table->string('external_url', 2048)->nullable()->after('source_type');
            }
        });

        DB::table('documents')
            ->whereNull('source_type')
            ->update(['source_type' => 'upload']);

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE `documents` MODIFY `storage_disk` VARCHAR(50) NULL DEFAULT 'documents'");
            DB::statement("ALTER TABLE `documents` MODIFY `storage_path` VARCHAR(500) NULL");
            DB::statement("ALTER TABLE `documents` MODIFY `original_name` VARCHAR(255) NULL");
            DB::statement("ALTER TABLE `documents` MODIFY `mime_type` VARCHAR(100) NULL");
            DB::statement("ALTER TABLE `documents` MODIFY `extension` VARCHAR(20) NULL");
            DB::statement("ALTER TABLE `documents` MODIFY `size_bytes` BIGINT UNSIGNED NULL");
            DB::statement("ALTER TABLE `documents` MODIFY `checksum_sha256` CHAR(64) NULL");
        } else {
            try {
                Schema::table('documents', function (Blueprint $table) {
                    $table->string('storage_disk', 50)->nullable()->default('documents')->change();
                    $table->string('storage_path', 500)->nullable()->change();
                    $table->string('original_name', 255)->nullable()->change();
                    $table->string('mime_type', 100)->nullable()->change();
                    $table->string('extension', 20)->nullable()->change();
                    $table->unsignedBigInteger('size_bytes')->nullable()->change();
                    $table->char('checksum_sha256', 64)->nullable()->change();
                });
            } catch (\Throwable $e) {
                // Best-effort only for non-MySQL drivers.
            }
        }

        try {
            Schema::table('documents', function (Blueprint $table) {
                $table->index('source_type', 'idx_documents_source_type');
            });
        } catch (\Throwable $e) {
            // Ignore duplicate index errors on reruns.
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('documents')) {
            return;
        }

        DB::table('documents')->whereNull('storage_disk')->update(['storage_disk' => 'documents']);
        DB::table('documents')->whereNull('storage_path')->update(['storage_path' => 'legacy/missing-file']);
        DB::table('documents')->whereNull('original_name')->update(['original_name' => 'missing-file']);
        DB::table('documents')->whereNull('mime_type')->update(['mime_type' => 'application/octet-stream']);
        DB::table('documents')->whereNull('extension')->update(['extension' => 'bin']);
        DB::table('documents')->whereNull('size_bytes')->update(['size_bytes' => 0]);
        DB::table('documents')->whereNull('checksum_sha256')->update(['checksum_sha256' => str_repeat('0', 64)]);

        try {
            Schema::table('documents', function (Blueprint $table) {
                $table->dropIndex('idx_documents_source_type');
            });
        } catch (\Throwable $e) {
            // Ignore if the index does not exist.
        }

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE `documents` MODIFY `storage_disk` VARCHAR(50) NOT NULL DEFAULT 'documents'");
            DB::statement("ALTER TABLE `documents` MODIFY `storage_path` VARCHAR(500) NOT NULL");
            DB::statement("ALTER TABLE `documents` MODIFY `original_name` VARCHAR(255) NOT NULL");
            DB::statement("ALTER TABLE `documents` MODIFY `mime_type` VARCHAR(100) NOT NULL");
            DB::statement("ALTER TABLE `documents` MODIFY `extension` VARCHAR(20) NOT NULL");
            DB::statement("ALTER TABLE `documents` MODIFY `size_bytes` BIGINT UNSIGNED NOT NULL");
            DB::statement("ALTER TABLE `documents` MODIFY `checksum_sha256` CHAR(64) NOT NULL");
        } else {
            try {
                Schema::table('documents', function (Blueprint $table) {
                    $table->string('storage_disk', 50)->default('documents')->nullable(false)->change();
                    $table->string('storage_path', 500)->nullable(false)->change();
                    $table->string('original_name', 255)->nullable(false)->change();
                    $table->string('mime_type', 100)->nullable(false)->change();
                    $table->string('extension', 20)->nullable(false)->change();
                    $table->unsignedBigInteger('size_bytes')->nullable(false)->change();
                    $table->char('checksum_sha256', 64)->nullable(false)->change();
                });
            } catch (\Throwable $e) {
                // Best-effort rollback only.
            }
        }

        Schema::table('documents', function (Blueprint $table) {
            if (Schema::hasColumn('documents', 'external_url')) {
                $table->dropColumn('external_url');
            }

            if (Schema::hasColumn('documents', 'source_type')) {
                $table->dropColumn('source_type');
            }
        });
    }
};
