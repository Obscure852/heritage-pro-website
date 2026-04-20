<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('health_incident_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 20)->unique();
            $table->enum('category', ['illness', 'injury', 'mental_health', 'medication', 'emergency', 'other']);
            $table->enum('severity', ['minor', 'moderate', 'serious', 'emergency'])->default('minor');
            $table->boolean('requires_parent_notification')->default(true);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index('code');
            $table->index('category');
            $table->index('severity');
            $table->index('active');
        });

        // Insert seed data
        $now = now();
        DB::table('health_incident_types')->insert([
            // Illness
            [
                'name' => 'Headache',
                'code' => 'HEADACHE',
                'category' => 'illness',
                'severity' => 'minor',
                'requires_parent_notification' => false,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Stomach Ache',
                'code' => 'STOMACH',
                'category' => 'illness',
                'severity' => 'minor',
                'requires_parent_notification' => false,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Fever',
                'code' => 'FEVER',
                'category' => 'illness',
                'severity' => 'moderate',
                'requires_parent_notification' => true,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Vomiting',
                'code' => 'VOMIT',
                'category' => 'illness',
                'severity' => 'moderate',
                'requires_parent_notification' => true,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Diarrhea',
                'code' => 'DIARRHEA',
                'category' => 'illness',
                'severity' => 'moderate',
                'requires_parent_notification' => true,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Infectious Disease',
                'code' => 'INFECTIOUS',
                'category' => 'illness',
                'severity' => 'serious',
                'requires_parent_notification' => true,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            // Injury
            [
                'name' => 'Minor Cut/Scrape',
                'code' => 'MINOR_CUT',
                'category' => 'injury',
                'severity' => 'minor',
                'requires_parent_notification' => false,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Bruise/Bump',
                'code' => 'BRUISE',
                'category' => 'injury',
                'severity' => 'minor',
                'requires_parent_notification' => false,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Sprain/Strain',
                'code' => 'SPRAIN',
                'category' => 'injury',
                'severity' => 'moderate',
                'requires_parent_notification' => true,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Deep Cut/Laceration',
                'code' => 'DEEP_CUT',
                'category' => 'injury',
                'severity' => 'serious',
                'requires_parent_notification' => true,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Suspected Fracture',
                'code' => 'FRACTURE',
                'category' => 'injury',
                'severity' => 'serious',
                'requires_parent_notification' => true,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Head Injury',
                'code' => 'HEAD_INJ',
                'category' => 'injury',
                'severity' => 'serious',
                'requires_parent_notification' => true,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            // Mental Health
            [
                'name' => 'Anxiety/Panic Attack',
                'code' => 'ANXIETY',
                'category' => 'mental_health',
                'severity' => 'moderate',
                'requires_parent_notification' => true,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Emotional Distress',
                'code' => 'DISTRESS',
                'category' => 'mental_health',
                'severity' => 'moderate',
                'requires_parent_notification' => true,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            // Medication
            [
                'name' => 'Scheduled Medication',
                'code' => 'SCHED_MED',
                'category' => 'medication',
                'severity' => 'minor',
                'requires_parent_notification' => false,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Emergency Medication',
                'code' => 'EMERG_MED',
                'category' => 'medication',
                'severity' => 'serious',
                'requires_parent_notification' => true,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            // Emergency
            [
                'name' => 'Allergic Reaction',
                'code' => 'ALLERGY',
                'category' => 'emergency',
                'severity' => 'emergency',
                'requires_parent_notification' => true,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Asthma Attack',
                'code' => 'ASTHMA',
                'category' => 'emergency',
                'severity' => 'emergency',
                'requires_parent_notification' => true,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Seizure',
                'code' => 'SEIZURE',
                'category' => 'emergency',
                'severity' => 'emergency',
                'requires_parent_notification' => true,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Loss of Consciousness',
                'code' => 'UNCONSCIOUS',
                'category' => 'emergency',
                'severity' => 'emergency',
                'requires_parent_notification' => true,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            // Other
            [
                'name' => 'Other Health Issue',
                'code' => 'OTHER',
                'category' => 'other',
                'severity' => 'minor',
                'requires_parent_notification' => true,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('health_incident_types');
    }
};
