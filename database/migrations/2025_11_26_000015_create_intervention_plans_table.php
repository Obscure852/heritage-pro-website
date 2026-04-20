<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('intervention_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('welfare_case_id')->constrained('welfare_cases')->cascadeOnDelete();
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();

            // Term context
            $table->foreignId('term_id')->constrained('terms')->restrictOnDelete();
            $table->integer('year');

            // Plan details
            $table->enum('plan_type', [
                'behavioral',
                'academic',
                'attendance',
                'social_emotional',
                'combined'
            ]);
            $table->string('title', 255);

            // Dates
            $table->date('start_date');
            $table->date('target_end_date');
            $table->date('actual_end_date')->nullable();
            $table->enum('review_frequency', [
                'weekly',
                'biweekly',
                'monthly',
                'termly'
            ])->default('monthly');
            $table->date('next_review_date')->nullable();

            // Goals (JSON array of goal objects)
            $table->json('goals')->nullable();

            // Strategies
            $table->text('strategies');
            $table->text('support_resources')->nullable();
            $table->text('accommodations')->nullable();

            // Stakeholders
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->json('assigned_staff')->nullable()->comment('Array of staff user IDs');
            $table->boolean('parent_involved')->default(true);
            $table->boolean('student_involved')->default(true);

            // Consent
            $table->boolean('parent_consent')->default(false);
            $table->date('parent_consent_date')->nullable();
            $table->boolean('student_agreement')->default(false);

            // Status
            $table->enum('status', [
                'draft',
                'pending_approval',
                'active',
                'completed',
                'discontinued',
                'extended'
            ])->default('draft');

            // Approval
            $table->boolean('requires_approval')->default(true);
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();

            // Outcome
            $table->enum('outcome', [
                'goals_met',
                'partial_progress',
                'no_progress',
                'discontinued',
                'ongoing'
            ])->nullable();
            $table->text('outcome_notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('student_id');
            $table->index(['plan_type', 'term_id'], 'ip_type_term_idx');
            $table->index(['status', 'term_id'], 'ip_status_term_idx');
            $table->index('next_review_date');
            $table->index('start_date');
            $table->index('target_end_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('intervention_plans');
    }
};
