<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{
   
    public function up(){
        Schema::create('klasses', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('term_id');
            $table->unsignedBigInteger('grade_id');
            $table->unsignedBigInteger('monitor_id')->nullable();
            $table->unsignedBigInteger('monitress_id')->nullable();
            $table->boolean('type')->nullable()->default(true);
            $table->boolean('active')->default(true);
            $table->year('year')->default(date('Y'));
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('user_id');
            $table->foreign('term_id')->references('id')->on('terms')->onDelete('cascade');
            $table->index('term_id');
            $table->foreign('grade_id')->references('id')->on('grades')->onDelete('restrict');
            $table->index('grade_id');

            $table->foreign('monitor_id')->references('id')->on('students')->onDelete('set null');
            $table->index('monitor_id');

            $table->foreign('monitress_id')->references('id')->on('students')->onDelete('set null');
            $table->index('monitress_id');
            
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(){
        Schema::dropIfExists('klasses');
    }
};
