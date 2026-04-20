<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Video transcoding jobs
        Schema::create('lms_video_transcoding_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('video_id')->constrained('lms_videos')->cascadeOnDelete();
            $table->string('format'); // 360p, 480p, 720p, 1080p, hls
            $table->string('codec')->default('h264'); // h264, h265, vp9
            $table->string('container')->default('mp4'); // mp4, webm, m3u8
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->string('input_path');
            $table->string('output_path')->nullable();
            $table->unsignedBigInteger('output_size')->nullable();
            $table->integer('bitrate')->nullable(); // kbps
            $table->string('resolution')->nullable(); // e.g., 1280x720
            $table->integer('progress')->default(0); // 0-100
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['video_id', 'status']);
            $table->index('status');
        });

        // Video qualities/renditions (completed transcodes)
        Schema::create('lms_video_qualities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('video_id')->constrained('lms_videos')->cascadeOnDelete();
            $table->string('label'); // e.g., "720p", "480p", "360p"
            $table->integer('width');
            $table->integer('height');
            $table->integer('bitrate'); // kbps
            $table->string('codec');
            $table->string('container');
            $table->string('file_path');
            $table->unsignedBigInteger('file_size');
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->unique(['video_id', 'label']);
            $table->index('video_id');
        });

        // Video chunks for HLS streaming
        Schema::create('lms_video_chunks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('video_id')->constrained('lms_videos')->cascadeOnDelete();
            $table->foreignId('quality_id')->nullable()->constrained('lms_video_qualities')->cascadeOnDelete();
            $table->integer('sequence');
            $table->decimal('duration', 8, 3); // seconds
            $table->string('file_path');
            $table->unsignedBigInteger('file_size');
            $table->timestamps();

            $table->index(['video_id', 'quality_id', 'sequence']);
        });

        // Add uploaded_by to videos table
        Schema::table('lms_videos', function (Blueprint $table) {
            $table->foreignId('uploaded_by')->nullable()->after('completion_threshold')->constrained('users')->nullOnDelete();
            $table->string('hls_playlist_path')->nullable()->after('transcoding_status');
            $table->integer('width')->nullable()->after('duration_seconds');
            $table->integer('height')->nullable()->after('width');
            $table->integer('bitrate')->nullable()->after('height');
            $table->string('codec')->nullable()->after('bitrate');
        });
    }

    public function down(): void
    {
        Schema::table('lms_videos', function (Blueprint $table) {
            $table->dropForeign(['uploaded_by']);
            $table->dropColumn(['uploaded_by', 'hls_playlist_path', 'width', 'height', 'bitrate', 'codec']);
        });

        Schema::dropIfExists('lms_video_chunks');
        Schema::dropIfExists('lms_video_qualities');
        Schema::dropIfExists('lms_video_transcoding_jobs');
    }
};
