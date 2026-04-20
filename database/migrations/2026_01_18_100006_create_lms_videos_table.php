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
        Schema::create('lms_videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_item_id')->constrained('lms_content_items')->onDelete('cascade');
            $table->enum('source_type', ['youtube', 'vimeo', 'upload', 'stream'])->default('youtube');
            $table->string('source_id')->nullable();
            $table->string('file_path')->nullable();
            $table->string('original_filename')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->bigInteger('file_size_bytes')->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->string('thumbnail_path')->nullable();
            $table->string('poster_path')->nullable();
            $table->enum('transcoding_status', ['pending', 'processing', 'completed', 'failed', 'not_required'])->default('not_required');
            $table->json('transcoded_formats')->nullable();
            $table->json('captions')->nullable();
            $table->longText('transcript')->nullable();
            $table->json('chapters')->nullable();
            $table->json('interactive_elements')->nullable();
            $table->integer('completion_threshold')->default(90);
            $table->timestamps();

            $table->index('source_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lms_videos');
    }
};
