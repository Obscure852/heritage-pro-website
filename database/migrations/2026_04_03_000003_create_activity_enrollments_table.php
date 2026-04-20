<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('activity_enrollments')) {
            return;
        }

        Schema::create('activity_enrollments', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('activity_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('term_id');
            $table->year('year');
            $table->string('status', 20)->default('active');
            $table->timestamp('joined_at')->nullable();
            $table->timestamp('left_at')->nullable();
            $table->unsignedBigInteger('joined_by')->nullable();
            $table->unsignedBigInteger('left_by')->nullable();
            $table->text('exit_reason')->nullable();
            $table->string('source', 30)->default('manual');
            $table->unsignedBigInteger('grade_id_snapshot')->nullable();
            $table->unsignedBigInteger('klass_id_snapshot')->nullable();
            $table->unsignedBigInteger('house_id_snapshot')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('activity_id')->references('id')->on('activities')->cascadeOnDelete();
            $table->foreign('student_id')->references('id')->on('students')->restrictOnDelete();
            $table->foreign('term_id')->references('id')->on('terms')->restrictOnDelete();
            $table->foreign('joined_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('left_by')->references('id')->on('users')->nullOnDelete();

            $table->index(['activity_id', 'term_id', 'status']);
            $table->index(['student_id', 'term_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_enrollments');
    }
};
