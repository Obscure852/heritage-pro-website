<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('crm_import_entity_locks')) {
            Schema::create('crm_import_entity_locks', function (Blueprint $table) {
                $table->string('entity', 40)->primary();
                $table->timestamps();
            });

            DB::table('crm_import_entity_locks')->insert([
                ['entity' => 'users', 'created_at' => now(), 'updated_at' => now()],
                ['entity' => 'leads', 'created_at' => now(), 'updated_at' => now()],
                ['entity' => 'customers', 'created_at' => now(), 'updated_at' => now()],
                ['entity' => 'contacts', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        if (! Schema::hasTable('crm_import_runs')) {
            Schema::create('crm_import_runs', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->string('entity', 40)->index();
                $table->string('status', 40)->index();
                $table->foreignId('initiated_by_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('disk', 40)->default('documents');
                $table->string('path');
                $table->string('original_filename');
                $table->string('file_checksum', 64)->index();
                $table->json('preview_summary')->nullable();
                $table->unsignedInteger('total_count')->default(0);
                $table->unsignedInteger('created_count')->default(0);
                $table->unsignedInteger('updated_count')->default(0);
                $table->unsignedInteger('skipped_count')->default(0);
                $table->unsignedInteger('failed_count')->default(0);
                $table->longText('passwords_payload')->nullable();
                $table->timestamp('passwords_downloaded_at')->nullable();
                $table->text('last_error')->nullable();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();

                $table->index(['entity', 'status']);
            });
        }

        if (! Schema::hasTable('crm_import_run_rows')) {
            Schema::create('crm_import_run_rows', function (Blueprint $table) {
                $table->id();
                $table->foreignId('import_run_id')->constrained('crm_import_runs')->cascadeOnDelete();
                $table->unsignedInteger('row_number');
                $table->string('normalized_key', 191)->nullable()->index();
                $table->string('action', 20)->nullable()->index();
                $table->json('payload')->nullable();
                $table->json('validation_errors')->nullable();
                $table->unsignedBigInteger('record_id')->nullable();
                $table->timestamp('processed_at')->nullable();
                $table->timestamps();

                $table->unique(['import_run_id', 'row_number'], 'crm_import_run_rows_unique_row');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_import_run_rows');
        Schema::dropIfExists('crm_import_runs');
        Schema::dropIfExists('crm_import_entity_locks');
    }
};
