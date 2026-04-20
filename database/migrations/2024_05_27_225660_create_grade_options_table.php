<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{

    public function up(){
        Schema::create('grade_options', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('grade_option_set_id');
            $table->integer('sequence');
            $table->string('label');
            $table->string('description');
            $table->timestamps();

            $table->foreign('grade_option_set_id')->references('id')->on('grade_option_sets')->onDelete('cascade');

            $table->index('label');
        });
    }

    public function down(){
        Schema::dropIfExists('grade_options');
    }
};