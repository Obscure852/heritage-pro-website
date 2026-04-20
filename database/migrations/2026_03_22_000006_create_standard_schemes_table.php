<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the standard_schemes table — the subject-level planning document
 * that is collaboratively created and then distributed to individual teachers.
 *
 * One standard scheme per subject + grade + term (unique constraint).
 */
return new class extends Migration {
    public function up(): void {
        Schema::create('standard_schemes', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('subject_id')->index();
            $table->unsignedBigInteger('grade_id')->index();
            $table->unsignedBigInteger('term_id')->index();
            $table->unsignedBigInteger('department_id')->index();
            $table->unsignedBigInteger('created_by')->index();
            $table->unsignedBigInteger('panel_lead_id')->nullable()->index();

            // Workflow status: draft, submitted, under_review, approved, revision_required
            $table->string('status', 30)->default('draft');
            $table->unsignedSmallInteger('total_weeks')->default(10);

            // Review fields
            $table->text('review_comments')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();

            // Publication fields
            $table->timestamp('published_at')->nullable();
            $table->unsignedBigInteger('published_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('subject_id')
                ->references('id')->on('subjects')
                ->onDelete('cascade');

            $table->foreign('grade_id')
                ->references('id')->on('grades')
                ->onDelete('cascade');

            $table->foreign('term_id')
                ->references('id')->on('terms')
                ->onDelete('cascade');

            $table->foreign('department_id')
                ->references('id')->on('departments')
                ->onDelete('cascade');

            $table->foreign('created_by')
                ->references('id')->on('users')
                ->onDelete('cascade');

            $table->foreign('panel_lead_id')
                ->references('id')->on('users')
                ->onDelete('set null');

            $table->foreign('reviewed_by')
                ->references('id')->on('users')
                ->onDelete('set null');

            $table->foreign('published_by')
                ->references('id')->on('users')
                ->onDelete('set null');

            // One standard scheme per subject + grade + term
            $table->unique(['subject_id', 'grade_id', 'term_id'], 'uniq_standard_scheme_subject_grade_term');
        });
    }

    public function down(): void {
        Schema::dropIfExists('standard_schemes');
    }
};
