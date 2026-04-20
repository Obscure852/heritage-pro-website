<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRolloverLogsTable extends Migration{

    public function up(){
        Schema::create('rollover_logs', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('from_term_id');
            $table->unsignedBigInteger('to_term_id');

            $table->json('actions');
            $table->timestamp('rollover_date');
            $table->boolean('is_undone')->default(false);
            $table->timestamps();

            $table->foreign('from_term_id')
                  ->references('id')
                  ->on('terms')
                  ->onDelete('cascade');

            $table->foreign('to_term_id')
                  ->references('id')
                  ->on('terms')
                  ->onDelete('cascade');
        });
    }


    public function down(){
        Schema::dropIfExists('rollover_logs');
    }
}
