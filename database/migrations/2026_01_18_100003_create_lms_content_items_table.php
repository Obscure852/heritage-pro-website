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
        Schema::create('lms_content_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained('lms_modules')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('content_type', [
                'text',
                'document',
                'video_youtube',
                'video_upload',
                'audio',
                'image',
                'scorm12',
                'scorm2004',
                'h5p',
                'quiz',
                'assignment',
                'live_session',
                'external_url',
                'lti_tool'
            ]);
            $table->json('content_data')->nullable();
            $table->integer('sequence')->default(0);
            $table->integer('duration_minutes')->nullable();
            $table->boolean('is_mandatory')->default(true);
            $table->decimal('passing_score', 5, 2)->nullable();
            $table->integer('max_attempts')->nullable();
            $table->json('unlock_conditions')->nullable();
            $table->dateTime('available_from')->nullable();
            $table->dateTime('available_until')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['module_id', 'sequence']);
            $table->index('content_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lms_content_items');
    }
};
