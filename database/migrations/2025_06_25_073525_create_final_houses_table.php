<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{

    public function up(){
        Schema::create('final_houses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('original_house_id');
            $table->string('name');
            $table->unsignedBigInteger('head');
            $table->unsignedBigInteger('assistant');
            $table->unsignedBigInteger('graduation_term_id');
            $table->year('graduation_year');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('head')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('assistant')->references('id')->on('users')->onDelete('cascade');
            $table->index(['graduation_term_id', 'graduation_year']);
            $table->index('original_house_id');
        });
    }

    public function down(){
        Schema::dropIfExists('final_houses');
    }
};