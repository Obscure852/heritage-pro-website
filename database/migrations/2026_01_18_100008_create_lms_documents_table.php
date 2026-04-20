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
        Schema::create('lms_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_item_id')->constrained('lms_content_items')->onDelete('cascade');
            $table->string('file_path');
            $table->string('original_filename');
            $table->string('mime_type', 100);
            $table->bigInteger('file_size_bytes');
            $table->enum('document_type', ['pdf', 'docx', 'pptx', 'xlsx', 'txt', 'other'])->default('pdf');
            $table->integer('page_count')->nullable();
            $table->json('preview_images')->nullable();
            $table->longText('text_content')->nullable();
            $table->boolean('allow_download')->default(true);
            $table->timestamps();

            $table->index('document_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lms_documents');
    }
};
