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
        Schema::create('parent_communications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('welfare_case_id')->nullable()->constrained('welfare_cases')->nullOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();

            // Term context
            $table->foreignId('term_id')->constrained('terms')->restrictOnDelete();
            $table->integer('year');

            // Communication details
            $table->date('communication_date');
            $table->time('communication_time')->nullable();

            // Type and method
            $table->enum('type', [
                'welfare_update',
                'concern',
                'positive_feedback',
                'meeting',
                'incident_notification',
                'general'
            ]);
            $table->enum('method', [
                'phone',
                'email',
                'sms',
                'in_person',
                'video_call',
                'letter',
                'home_visit'
            ]);

            // Direction
            $table->enum('direction', ['outbound', 'inbound']);

            // Participants
            $table->foreignId('staff_member_id')->constrained('users')->restrictOnDelete();
            $table->string('parent_guardian_name', 255)->nullable();
            $table->string('relationship', 50)->nullable();
            $table->string('contact_used', 255)->nullable();

            // Content
            $table->string('subject', 255);
            $table->text('summary');
            $table->text('detailed_notes')->nullable();

            // Meeting specific
            $table->string('meeting_location', 255)->nullable();
            $table->integer('meeting_duration_minutes')->nullable();
            $table->text('meeting_attendees')->nullable();

            // Outcome
            $table->text('outcome')->nullable();
            $table->text('action_items')->nullable();
            $table->boolean('follow_up_required')->default(false);
            $table->date('follow_up_date')->nullable();
            $table->boolean('follow_up_completed')->default(false);

            // Response
            $table->text('parent_response')->nullable();
            $table->text('parent_concerns')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['student_id', 'term_id'], 'pc_student_term_idx');
            $table->index(['type', 'term_id'], 'pc_type_term_idx');
            $table->index('communication_date');
            $table->index('staff_member_id');
            $table->index(['follow_up_required', 'follow_up_date'], 'pc_followup_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parent_communications');
    }
};
