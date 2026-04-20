<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // H5P Content packages
        Schema::create('lms_h5p_contents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('library'); // e.g., "H5P.InteractiveVideo 1.21"
            $table->string('library_major_version')->nullable();
            $table->string('library_minor_version')->nullable();
            $table->json('parameters'); // H5P content parameters
            $table->string('embed_type')->default('div'); // div or iframe
            $table->string('content_path')->nullable(); // Path to extracted content
            $table->string('package_path')->nullable(); // Path to original .h5p file
            $table->unsignedBigInteger('package_size')->nullable();
            $table->boolean('disable')->default(false);
            $table->string('slug')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('library');
        });

        // H5P Results/Attempts
        Schema::create('lms_h5p_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('h5p_content_id')->constrained('lms_h5p_contents')->cascadeOnDelete();
            $table->foreignId('content_item_id')->nullable()->constrained('lms_content_items')->nullOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->unsignedInteger('score')->nullable();
            $table->unsignedInteger('max_score')->nullable();
            $table->unsignedInteger('opened')->default(0); // Times opened
            $table->unsignedInteger('finished')->default(0); // Times finished
            $table->unsignedInteger('time_spent')->default(0); // Total seconds spent
            $table->timestamp('first_opened_at')->nullable();
            $table->timestamp('last_opened_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['h5p_content_id', 'student_id']);
        });

        // H5P xAPI Events (for detailed interaction tracking)
        Schema::create('lms_h5p_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('h5p_content_id')->constrained('lms_h5p_contents')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('verb'); // xAPI verb (answered, completed, progressed, etc.)
            $table->string('object_type')->nullable();
            $table->string('object_id')->nullable();
            $table->json('result')->nullable(); // Score, success, response, etc.
            $table->json('context')->nullable(); // Additional context
            $table->timestamp('created_at');

            $table->index(['h5p_content_id', 'student_id']);
            $table->index('verb');
        });

        // H5P Libraries (for self-hosted installations)
        Schema::create('lms_h5p_libraries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('title');
            $table->unsignedInteger('major_version');
            $table->unsignedInteger('minor_version');
            $table->unsignedInteger('patch_version');
            $table->boolean('runnable')->default(false);
            $table->boolean('fullscreen')->default(false);
            $table->string('embed_types')->nullable();
            $table->json('preloaded_js')->nullable();
            $table->json('preloaded_css')->nullable();
            $table->text('semantics')->nullable();
            $table->string('tutorial_url')->nullable();
            $table->boolean('has_icon')->default(false);
            $table->timestamps();

            $table->unique(['name', 'major_version', 'minor_version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lms_h5p_events');
        Schema::dropIfExists('lms_h5p_results');
        Schema::dropIfExists('lms_h5p_libraries');
        Schema::dropIfExists('lms_h5p_contents');
    }
};
