<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{

    public function up(){
        Schema::create('klass_subject', function (Blueprint $table){
            $table->id();
            $table->unsignedBigInteger('klass_id');
            $table->unsignedBigInteger('grade_subject_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('term_id');
            $table->unsignedBigInteger('grade_id');
            $table->unsignedBigInteger('venue_id')->nullable();
            $table->year('year')->default(date('Y'));
            $table->boolean('active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('klass_id')->references('id')->on('klasses')->onDelete('cascade');
            $table->foreign('grade_subject_id')->references('id')->on('grade_subject')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('term_id')->references('id')->on('terms')->onDelete('cascade');
            $table->foreign('grade_id')->references('id')->on('grades')->onDelete('cascade');
            $table->foreign('venue_id')->references('id')->on('venues')->onDelete('cascade');

            $table->index('klass_id');
            $table->index('grade_subject_id');
            $table->index('user_id');
            $table->index('term_id');
            $table->index('grade_id');
            $table->index('venue_id');

            $table->index('year');
            $table->index('active');
        });
    }

    public function down(){
        Schema::dropIfExists('klass_subject');
    }
};
