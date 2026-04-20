<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{
    public function up(): void{
        Schema::create('external_exam_subject_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('school_type', 30)->nullable();
            $table->string('exam_type', 20);
            $table->string('source_key', 100);
            $table->string('source_code', 50)->nullable();
            $table->string('source_label', 255);
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['school_type', 'exam_type', 'source_key'], 'uniq_exam_subject_mapping');
            $table->index(['exam_type', 'school_type'], 'idx_exam_school_type');

            $table->foreign('subject_id')
                ->references('id')
                ->on('subjects')
                ->nullOnDelete();
        });
    }

    public function down(): void{
        Schema::dropIfExists('external_exam_subject_mappings');
    }
};
