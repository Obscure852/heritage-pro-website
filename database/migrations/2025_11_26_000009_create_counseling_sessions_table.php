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
        Schema::create('counseling_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('welfare_case_id')->constrained('welfare_cases')->cascadeOnDelete();
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->foreignId('counsellor_id')->constrained('users')->restrictOnDelete();

            // Term context
            $table->foreignId('term_id')->constrained('terms')->restrictOnDelete();
            $table->integer('year');

            // Session details
            $table->date('session_date');
            $table->time('session_time')->nullable();
            // Virtual column for duplicate checks at application level
            // This column normalizes NULL session_time to '00:00:00' for easier comparison
            $table->string('session_time_key', 8)
                ->virtualAs("COALESCE(session_time, '00:00:00')");
            $table->integer('duration_minutes')->nullable();
            $table->integer('session_number')->default(1);

            // Type and source
            $table->enum('session_type', [
                'individual',
                'group',
                'family',
                'crisis',
                'follow_up'
            ]);
            $table->enum('status', [
                'scheduled',
                'completed',
                'cancelled',
                'no_show'
            ])->default('scheduled');
            // Confidentiality level - default to Level 4 (Highly Confidential) for counseling
            $table->tinyInteger('confidentiality_level')->default(4)
                ->comment('2=Restricted, 3=Confidential, 4=Highly Confidential');
            $table->enum('referral_source', [
                'self',
                'teacher',
                'parent',
                'admin',
                'peer',
                'external'
            ]);

            // Content (ENCRYPTED - Level 4 Confidential)
            // These fields will be encrypted by the model's Encryptable trait
            $table->text('presenting_issue')->nullable();
            $table->text('session_notes')->nullable();
            $table->text('interventions_used')->nullable();
            $table->text('goals_discussed')->nullable();
            $table->string('student_mood', 50)->nullable();
            $table->enum('risk_assessment', [
                'none',
                'low',
                'moderate',
                'high',
                'immediate'
            ])->default('none');

            // Recommendations
            $table->text('recommendations')->nullable();
            $table->text('homework_assigned')->nullable();

            // Follow-up
            $table->boolean('follow_up_required')->default(false);
            $table->date('next_session_date')->nullable();

            // External referral
            $table->boolean('external_referral_made')->default(false);
            $table->string('external_agency', 255)->nullable();
            $table->text('referral_details')->nullable();

            // Consent
            $table->boolean('student_consent')->default(true);
            $table->boolean('parent_informed')->default(false);
            $table->boolean('parent_consent')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['counsellor_id', 'term_id'], 'cs_counselor_term_idx');
            $table->index('session_date');
            $table->index(['term_id', 'year'], 'cs_term_year_idx');
            $table->index('risk_assessment');
            $table->index('status');
            $table->index(['follow_up_required', 'next_session_date'], 'cs_followup_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('counseling_sessions');
    }
};
