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
        Schema::create('disciplinary_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('welfare_case_id')->constrained('welfare_cases')->cascadeOnDelete();
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->foreignId('incident_type_id')->constrained('disciplinary_incident_types')->restrictOnDelete();

            // Term context
            $table->foreignId('term_id')->constrained('terms')->restrictOnDelete();
            $table->integer('year');

            // Incident details
            $table->date('incident_date');
            $table->time('incident_time')->nullable();
            $table->string('location', 255)->nullable();
            $table->text('description');
            $table->text('evidence')->nullable();

            // People involved
            $table->foreignId('reported_by')->constrained('users')->restrictOnDelete();
            $table->json('witnesses')->nullable()->comment('Array of witness names');
            $table->json('other_students_involved')->nullable()->comment('Array of student IDs');

            // Action taken
            $table->foreignId('action_id')->nullable()->constrained('disciplinary_actions')->restrictOnDelete();
            $table->text('action_notes')->nullable();
            $table->date('action_start_date')->nullable();
            $table->date('action_end_date')->nullable();
            $table->integer('duration_days')->nullable();

            // Parent notification
            $table->boolean('parent_notified')->default(false);
            $table->timestamp('parent_notified_at')->nullable();
            $table->foreignId('parent_notified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('notification_method', [
                'phone',
                'email',
                'letter',
                'in_person',
                'sms'
            ])->nullable();
            $table->text('parent_response')->nullable();

            // Resolution
            $table->enum('status', [
                'reported',
                'investigating',
                'pending_action',
                'action_in_progress',
                'resolved',
                'appealed'
            ])->default('reported');
            $table->text('resolution')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();

            // Appeal
            $table->boolean('appeal_filed')->default(false);
            $table->date('appeal_date')->nullable();
            $table->enum('appeal_outcome', [
                'upheld',
                'modified',
                'overturned'
            ])->nullable();
            $table->text('appeal_notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['incident_type_id', 'term_id'], 'dr_type_term_idx');
            $table->index(['action_id', 'term_id'], 'dr_action_term_idx');
            $table->index(['status', 'term_id'], 'dr_status_term_idx');
            $table->index('incident_date');
            $table->index(['action_start_date', 'action_end_date'], 'dr_suspension_period_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disciplinary_records');
    }
};
