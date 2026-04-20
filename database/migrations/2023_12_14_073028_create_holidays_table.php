<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{

    public function up(){
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedBigInteger('term_id');
            $table->year('year');

            $table->foreign('term_id')->references('id')->on('terms');

            $table->index('term_id');
            $table->index('name');

            $table->softDeletes();
            $table->timestamps();
        });
    }


    public function down(){
        Schema::dropIfExists('holidays');
    }
};
