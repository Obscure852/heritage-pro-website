<?php

use App\Models\Subject;
use App\Support\AcademicStructureRegistry;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('subjects', function (Blueprint $table): void {
            if (!Schema::hasColumn('subjects', 'canonical_key')) {
                $table->string('canonical_key')->nullable()->after('name')->index();
            }
        });

        Subject::query()->withTrashed()->get()->each(function (Subject $subject): void {
            $canonicalKey = AcademicStructureRegistry::canonicalKeyFor($subject->level, $subject->abbrev, $subject->name);

            if ($canonicalKey && $subject->canonical_key !== $canonicalKey) {
                $subject->canonical_key = $canonicalKey;
                $subject->saveQuietly();
            }
        });

        Schema::create('migration_batches', function (Blueprint $table): void {
            $table->id();
            $table->string('mode')->index();
            $table->string('status')->default('pending')->index();
            $table->json('summary')->nullable();
            $table->timestamps();
        });

        Schema::create('migration_id_maps', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('migration_batch_id')->constrained('migration_batches')->cascadeOnDelete();
            $table->string('entity_type')->index();
            $table->string('source_system')->index();
            $table->string('source_identifier');
            $table->unsignedBigInteger('target_identifier')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['migration_batch_id', 'entity_type', 'source_system', 'source_identifier'], 'migration_id_maps_unique_source');
        });

        Schema::create('migration_conflicts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('migration_batch_id')->constrained('migration_batches')->cascadeOnDelete();
            $table->string('entity_type')->index();
            $table->string('conflict_type')->index();
            $table->string('status')->default('open')->index();
            $table->json('details');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('migration_conflicts');
        Schema::dropIfExists('migration_id_maps');
        Schema::dropIfExists('migration_batches');

        Schema::table('subjects', function (Blueprint $table): void {
            if (Schema::hasColumn('subjects', 'canonical_key')) {
                $table->dropColumn('canonical_key');
            }
        });
    }
};
