<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lms_assignment_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained('lms_assignments')->cascadeOnDelete();
            $table->string('label')->nullable();
            $table->string('original_name');
            $table->string('file_path');
            $table->string('mime_type');
            $table->unsignedBigInteger('file_size');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['assignment_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lms_assignment_attachments');
    }
};
