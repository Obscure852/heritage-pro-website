<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{
    public function up(){
        Schema::create('performance_targets', function (Blueprint $table) {
            $table->id();
            $table->year('academic_year');
            $table->enum('exam_type', ['JCE', 'BGCSE', 'PSLE']);
            
            $table->decimal('high_achievement_target', 5, 2)->comment('High performers target %');
            $table->string('high_achievement_label')->comment('Label for high achievement (e.g., M-B%, A-B%)');
            
            $table->decimal('pass_rate_target', 5, 2)->comment('Pass rate target %');
            $table->string('pass_rate_label')->comment('Label for pass rate (e.g., M-C%, A-C%)');
            
            $table->decimal('non_failure_target', 5, 2)->comment('Non-failure rate target %');
            $table->string('non_failure_label')->comment('Label for non-failure (e.g., M-D%, A-D%)');
            
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->unique(['academic_year', 'exam_type']);
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
            
            $table->index(['academic_year', 'exam_type']);
        });
    }

    public function down(){
        Schema::dropIfExists('performance_targets');
    }
};