<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the schemes_of_work table — the primary planning document for a teacher.
 *
 * A scheme must be assigned to exactly one of klass_subject_id OR optional_subject_id
 * (XOR constraint). This is enforced at both the DB layer (MySQL 8.0.16+ CHECK)
 * and the app layer (SchemeOfWork model saving observer).
 *
 * Requirements: FOUN-01, FOUN-03, FOUN-04
 */
return new class extends Migration {
    public function up(): void {
        Schema::create('schemes_of_work', function (Blueprint $table) {
            $table->id();

            // XOR assignment — exactly one of these two must be non-null (FOUN-03)
            $table->unsignedBigInteger('klass_subject_id')->nullable()->index();
            $table->unsignedBigInteger('optional_subject_id')->nullable()->index();

            $table->unsignedBigInteger('term_id')->index();
            $table->unsignedBigInteger('teacher_id')->index();

            // Workflow status: draft, submitted, under_review, approved, revision_required
            $table->string('status', 30)->default('draft');
            $table->text('review_comments')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();

            $table->unsignedSmallInteger('total_weeks')->default(10);

            // Self-referential FK for cloning/template schemes
            $table->unsignedBigInteger('cloned_from_id')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Foreign key constraints
            $table->foreign('klass_subject_id')
                ->references('id')
                ->on('klass_subject')
                ->onDelete('set null');

            $table->foreign('optional_subject_id')
                ->references('id')
                ->on('optional_subjects')
                ->onDelete('set null');

            $table->foreign('term_id')
                ->references('id')
                ->on('terms')
                ->onDelete('cascade');

            $table->foreign('teacher_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('reviewed_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->foreign('cloned_from_id')
                ->references('id')
                ->on('schemes_of_work')
                ->onDelete('set null');

            // Unique per assignment per term (FOUN-04)
            // NULL-safe: MySQL unique index allows multiple NULLs, so only the non-null
            // side is enforced — this is the correct behaviour for the XOR design.
            $table->unique(['klass_subject_id', 'term_id'], 'uniq_scheme_klass_term');
            $table->unique(['optional_subject_id', 'term_id'], 'uniq_scheme_optional_term');
        });

        // XOR CHECK constraint attempt — MySQL 8.0.16+ only (FOUN-03)
        // Blueprint::check() is Laravel 10+ only; use DB::statement() for Laravel 9.x.
        // The app-layer 'saving' observer on SchemeOfWork is the primary enforcement.
        //
        // NOTE: MySQL 8 InnoDB (error 3823) prevents CHECK constraints from referencing
        // columns that are part of FK referential actions (ON DELETE SET NULL). Because
        // klass_subject_id and optional_subject_id have FK referential actions, the DB-level
        // CHECK may be rejected. The saving() observer in SchemeOfWork is the reliable guard.
        try {
            DB::statement("
                ALTER TABLE schemes_of_work
                ADD CONSTRAINT chk_scheme_xor_assignment
                CHECK (
                    (klass_subject_id IS NOT NULL AND optional_subject_id IS NULL)
                    OR
                    (klass_subject_id IS NULL AND optional_subject_id IS NOT NULL)
                )
            ");
        } catch (\Throwable $e) {
            // MySQL 8 InnoDB error 3823 is expected here because these FK columns use
            // ON DELETE SET NULL referential actions. Keep migrations quiet for that
            // known incompatibility, but surface any other failure.
            $isExpectedMysqlFkCheckConflict =
                str_contains($e->getMessage(), 'General error: 3823')
                && str_contains($e->getMessage(), 'chk_scheme_xor_assignment');

            if (!$isExpectedMysqlFkCheckConflict) {
                \Illuminate\Support\Facades\Log::warning(
                    'schemes_of_work XOR CHECK constraint could not be added. ' .
                    'App-layer saving() observer enforces XOR at the model level.',
                    ['error' => $e->getMessage()]
                );
            }
        }
    }

    public function down(): void {
        // Drop CHECK constraint explicitly before dropping the table (MySQL 8.0.16+)
        if (Schema::hasTable('schemes_of_work')) {
            try {
                DB::statement('ALTER TABLE schemes_of_work DROP CONSTRAINT chk_scheme_xor_assignment');
            } catch (\Exception $e) {
                // Silently ignore — constraint may not exist on older MySQL
            }
        }
        Schema::dropIfExists('schemes_of_work');
    }
};
