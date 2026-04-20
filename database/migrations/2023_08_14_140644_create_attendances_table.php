<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('klass_id');
            $table->unsignedBigInteger('term_id');
            $table->date('date')->nullable();
            $table->string('status')->nullable();
            $table->year('year')->default(date('Y'));

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('klass_id')->references('id')->on('klasses')->onDelete('cascade');
            $table->foreign('term_id')->references('id')->on('terms')->onDelete('cascade');

            $table->index('student_id');
            $table->index('klass_id');
            $table->index('term_id');
            $table->index('date');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('attendances');
    }
};
