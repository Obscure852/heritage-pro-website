<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{

    public function up(){
        Schema::create('houses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('head');
            $table->unsignedBigInteger('assistant');
            $table->unsignedBigInteger('term_id');
            $table->year('year');


            $table->foreign('head')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('assistant')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('term_id')->references('id')->on('terms')->onDelete('cascade');

            $table->timestamps();
            $table->index('name');
            $table->index(['head', 'assistant']);
        });
    }

    public function down(){
        Schema::dropIfExists('houses');
    }
};