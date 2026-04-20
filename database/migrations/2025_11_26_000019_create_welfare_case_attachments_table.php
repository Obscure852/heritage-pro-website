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
        Schema::create('welfare_case_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('welfare_case_id')->constrained('welfare_cases')->cascadeOnDelete();

            // Term context
            $table->foreignId('term_id')->constrained('terms')->restrictOnDelete();
            $table->integer('year');

            $table->string('file_name', 255);
            $table->string('original_name', 255)->nullable();
            $table->string('file_path', 500);
            $table->string('file_type', 50)->nullable();
            $table->integer('file_size')->nullable()->comment('Size in bytes');

            $table->text('description')->nullable();
            $table->boolean('is_confidential')->default(false);

            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['welfare_case_id', 'term_id'], 'wca_case_term_idx');
            $table->index('file_type');
            $table->index('is_confidential');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('welfare_case_attachments');
    }
};
