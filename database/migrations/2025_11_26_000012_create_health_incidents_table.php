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
        Schema::create('health_incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('welfare_case_id')->constrained('welfare_cases')->cascadeOnDelete();
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->foreignId('incident_type_id')->constrained('health_incident_types')->restrictOnDelete();

            // Term context
            $table->foreignId('term_id')->constrained('terms')->restrictOnDelete();
            $table->integer('year');

            // Incident details
            $table->date('incident_date');
            $table->time('incident_time');
            $table->string('location', 255)->nullable();

            // Description
            $table->text('description');
            $table->text('symptoms')->nullable();
            $table->text('treatment_given')->nullable();
            $table->foreignId('treated_by')->nullable()->constrained('users')->nullOnDelete();

            // Response
            $table->boolean('first_aid_given')->default(false);
            $table->text('first_aid_details')->nullable();
            $table->foreignId('reported_by')->constrained('users')->restrictOnDelete();

            // Vitals (optional)
            $table->decimal('temperature', 4, 1)->nullable();
            $table->string('blood_pressure', 20)->nullable();
            $table->integer('pulse')->nullable();

            // Medication
            $table->boolean('medication_administered')->default(false);
            $table->text('medication_details')->nullable();
            $table->string('medication_name', 255)->nullable();
            $table->string('medication_dose', 100)->nullable();
            $table->time('medication_time')->nullable();

            // Outcome
            $table->enum('outcome', [
                'returned_to_class',
                'rested_and_returned',
                'sent_home',
                'hospital',
                'ambulance'
            ]);
            $table->integer('rest_duration_minutes')->nullable();
            $table->boolean('sent_home')->default(false);
            $table->timestamp('sent_home_at')->nullable();
            $table->string('collected_by', 255)->nullable();

            // Parent notification
            $table->boolean('parent_notified')->default(false);
            $table->timestamp('parent_notified_at')->nullable();
            $table->foreignId('parent_contacted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('contact_method', ['phone', 'sms', 'email'])->nullable();
            $table->text('parent_response')->nullable();

            // Hospital/Ambulance
            $table->boolean('hospital_referral')->default(false);
            $table->boolean('hospital_visit')->default(false);
            $table->string('hospital_name', 255)->nullable();
            $table->text('hospital_notes')->nullable();
            $table->boolean('ambulance_called')->default(false);
            $table->time('ambulance_time')->nullable();
            $table->foreignId('accompanied_by')->nullable()->constrained('users')->nullOnDelete();

            // Follow-up
            $table->boolean('follow_up_required')->default(false);
            $table->text('follow_up_notes')->nullable();
            $table->date('follow_up_date')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['incident_type_id', 'term_id'], 'hi_type_term_idx');
            $table->index(['outcome', 'term_id'], 'hi_outcome_term_idx');
            $table->index('incident_date');
            $table->index(['incident_date', 'incident_time'], 'hi_datetime_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('health_incidents');
    }
};
