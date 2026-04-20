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
        Schema::create('welfare_cases', function (Blueprint $table) {
            $table->id();

            // Unique case number with index for race-condition-proof generation
            $table->string('case_number', 30)->unique();

            // Core relationships
            $table->foreignId('student_id')->constrained('students')->restrictOnDelete();
            $table->foreignId('welfare_type_id')->constrained('welfare_types')->restrictOnDelete();

            // Term context - required for all welfare records
            $table->foreignId('term_id')->constrained('terms')->restrictOnDelete();
            $table->integer('year');

            // Status tracking
            $table->enum('status', [
                'open',
                'in_progress',
                'pending_approval',
                'resolved',
                'closed',
                'escalated'
            ])->default('open');
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');

            // Assignment
            $table->foreignId('opened_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();

            // Dates
            $table->date('incident_date')->nullable();
            $table->timestamp('opened_at')->useCurrent();
            $table->timestamp('closed_at')->nullable();

            // Summary
            $table->string('title', 255);
            $table->text('summary')->nullable();

            // Approval workflow
            $table->boolean('requires_approval')->default(false);
            $table->enum('approval_status', [
                'not_required',
                'pending',
                'approved',
                'rejected'
            ])->default('not_required');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();

            // Linking for related cases
            $table->foreignId('parent_case_id')->nullable()->constrained('welfare_cases')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Indexes for common queries
            $table->index(['student_id', 'term_id'], 'wc_student_term_idx');
            $table->index(['status', 'term_id'], 'wc_status_term_idx');
            $table->index(['welfare_type_id', 'term_id'], 'wc_type_term_idx');
            $table->index(['assigned_to', 'status'], 'wc_assigned_status_idx');
            $table->index(['approval_status', 'term_id'], 'wc_approval_term_idx');
            $table->index('year');
            $table->index('priority');
            $table->index('incident_date');
            $table->index('opened_at');

            // Unique constraint to prevent duplicate welfare cases for the same
            // student + welfare type + term + incident date combination.
            // Note: MySQL treats NULL values as unique, so NULL incident_date values
            // won't trigger the constraint. The application layer handles NULL dates.
            $table->unique(
                ['student_id', 'welfare_type_id', 'term_id', 'incident_date'],
                'unique_student_type_term_date'
            );
        });

        // Create sequence table for race-condition-proof case number generation
        Schema::create('welfare_case_sequences', function (Blueprint $table) {
            $table->id();
            $table->integer('year')->unique();
            $table->integer('last_sequence')->default(0);
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('welfare_case_sequences');
        Schema::dropIfExists('welfare_cases');
    }
};
