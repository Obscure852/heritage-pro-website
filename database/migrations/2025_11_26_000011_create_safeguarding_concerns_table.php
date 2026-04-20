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
        Schema::create('safeguarding_concerns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('welfare_case_id')->constrained('welfare_cases')->cascadeOnDelete();
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->foreignId('category_id')->constrained('safeguarding_categories')->restrictOnDelete();

            // Term context
            $table->foreignId('term_id')->constrained('terms')->restrictOnDelete();
            $table->integer('year');

            // Concern details
            $table->date('date_identified');
            $table->time('concern_time')->nullable();

            // Risk assessment
            $table->enum('risk_level', ['low', 'medium', 'high', 'critical']);
            $table->boolean('immediate_danger')->default(false);

            // Description (ENCRYPTED - Level 4 Confidential)
            // These fields will be encrypted by the model's Encryptable trait
            $table->text('disclosure_details')->nullable();
            $table->text('physical_indicators')->nullable();
            $table->text('behavioral_indicators')->nullable();
            $table->text('additional_context')->nullable();
            $table->boolean('immediate_action_taken')->default(false);
            $table->text('concern_details')->nullable();
            $table->text('indicators_observed')->nullable();

            // Source
            $table->enum('source_of_concern', [
                'disclosure',
                'observation',
                'third_party',
                'anonymous',
                'referral'
            ])->nullable();
            $table->foreignId('reported_by')->constrained('users')->restrictOnDelete();

            // Designated Safeguarding Lead
            $table->foreignId('designated_lead_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('lead_notified_at')->nullable();

            // Actions taken
            $table->text('immediate_action_details')->nullable();
            $table->text('safety_plan')->nullable();

            // External agencies
            $table->boolean('authorities_notified')->default(false);
            $table->timestamp('authorities_notified_at')->nullable();
            $table->string('authority_name', 255)->nullable();
            $table->string('authority_reference', 100)->nullable();
            $table->string('authority_contact', 255)->nullable();

            // Parent/Guardian notification
            $table->boolean('parents_informed')->default(false);
            $table->timestamp('parents_informed_at')->nullable();
            $table->text('reason_parent_not_informed')->nullable();
            $table->text('parent_response')->nullable();

            // Case management
            $table->enum('status', [
                'identified',
                'investigating',
                'referred',
                'monitoring',
                'closed'
            ])->default('identified');
            $table->date('review_date')->nullable();
            $table->text('outcome')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['category_id', 'term_id'], 'sg_category_term_idx');
            $table->index(['risk_level', 'term_id'], 'sg_risk_term_idx');
            $table->index(['status', 'term_id'], 'sg_status_term_idx');
            $table->index('immediate_danger');
            $table->index('review_date');
            $table->index('date_identified');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('safeguarding_concerns');
    }
};
