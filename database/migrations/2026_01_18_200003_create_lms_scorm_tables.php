<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // SCORM Packages - uploaded SCORM 1.2/2004 packages
        Schema::create('lms_scorm_packages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('version', ['1.2', '2004_2nd', '2004_3rd', '2004_4th'])->default('1.2');

            // Package files
            $table->string('zip_path'); // Original uploaded zip
            $table->string('extracted_path'); // Extracted folder path
            $table->string('launch_url'); // Entry point URL (from manifest)
            $table->string('identifier')->nullable(); // From manifest

            // Manifest data
            $table->json('manifest_data')->nullable(); // Parsed imsmanifest.xml
            $table->json('organizations')->nullable(); // Course structure
            $table->json('resources')->nullable(); // Resource list

            // Settings
            $table->integer('mastery_score')->nullable(); // Passing score %
            $table->integer('time_limit_minutes')->nullable();
            $table->boolean('allow_review')->default(true);
            $table->integer('max_attempts')->nullable();

            // Metadata
            $table->bigInteger('package_size')->nullable();
            $table->foreignId('uploaded_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index('version');
        });

        // SCORM Attempts - student attempts at SCORM content
        Schema::create('lms_scorm_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained('lms_scorm_packages')->cascadeOnDelete();
            $table->foreignId('content_item_id')->nullable()->constrained('lms_content_items')->nullOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->integer('attempt_number')->default(1);

            // SCORM status
            $table->string('lesson_status')->nullable(); // passed, failed, completed, incomplete, browsed, not attempted
            $table->string('exit_status')->nullable(); // timeout, suspend, logout, normal
            $table->string('entry')->nullable(); // ab-initio, resume, ""
            $table->string('credit')->default('credit'); // credit, no-credit

            // Score
            $table->decimal('score_raw', 8, 2)->nullable();
            $table->decimal('score_min', 8, 2)->nullable();
            $table->decimal('score_max', 8, 2)->nullable();
            $table->decimal('score_scaled', 5, 4)->nullable(); // -1 to 1 for SCORM 2004

            // Progress
            $table->string('progress_measure')->nullable(); // 0-1 for SCORM 2004
            $table->string('completion_status')->nullable(); // completed, incomplete, not attempted, unknown
            $table->string('success_status')->nullable(); // passed, failed, unknown

            // Time tracking
            $table->string('total_time')->nullable(); // ISO 8601 duration
            $table->string('session_time')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('last_accessed_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            // Suspend data for resume functionality
            $table->text('suspend_data')->nullable();
            $table->string('location')->nullable(); // bookmark/location

            $table->timestamps();

            $table->unique(['package_id', 'student_id', 'attempt_number']);
            $table->index(['student_id', 'package_id']);
        });

        // SCORM Runtime Data - stores all cmi data elements
        Schema::create('lms_scorm_cmi_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')->constrained('lms_scorm_attempts')->cascadeOnDelete();
            $table->string('element'); // cmi.core.lesson_status, cmi.suspend_data, etc.
            $table->text('value')->nullable();
            $table->timestamps();

            $table->unique(['attempt_id', 'element']);
            $table->index('attempt_id');
        });

        // SCORM Interactions - tracks interactions/questions answered
        Schema::create('lms_scorm_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')->constrained('lms_scorm_attempts')->cascadeOnDelete();
            $table->string('interaction_id'); // Identifier from SCORM package
            $table->string('type')->nullable(); // true-false, choice, fill-in, matching, etc.
            $table->text('description')->nullable();
            $table->text('correct_responses')->nullable(); // JSON array
            $table->text('learner_response')->nullable();
            $table->string('result')->nullable(); // correct, incorrect, neutral, etc.
            $table->decimal('weighting', 5, 2)->nullable();
            $table->string('latency')->nullable(); // Time to respond (ISO 8601)
            $table->timestamp('timestamp')->nullable();
            $table->timestamps();

            $table->index(['attempt_id', 'interaction_id']);
        });

        // SCORM Objectives - tracks objective completion
        Schema::create('lms_scorm_objectives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')->constrained('lms_scorm_attempts')->cascadeOnDelete();
            $table->string('objective_id');
            $table->string('status')->nullable(); // passed, failed, completed, etc.
            $table->decimal('score_raw', 8, 2)->nullable();
            $table->decimal('score_min', 8, 2)->nullable();
            $table->decimal('score_max', 8, 2)->nullable();
            $table->decimal('score_scaled', 5, 4)->nullable();
            $table->string('completion_status')->nullable();
            $table->string('success_status')->nullable();
            $table->string('progress_measure')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['attempt_id', 'objective_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lms_scorm_objectives');
        Schema::dropIfExists('lms_scorm_interactions');
        Schema::dropIfExists('lms_scorm_cmi_data');
        Schema::dropIfExists('lms_scorm_attempts');
        Schema::dropIfExists('lms_scorm_packages');
    }
};
