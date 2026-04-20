<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{

    public function up(){

        Schema::create('rollover_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_term_id')->constrained('terms');
            $table->foreignId('to_term_id')->constrained('terms');
            $table->enum('status', ['completed', 'reversed', 'in_progress', 'failed']);
            $table->datetime('rollover_timestamp');
            $table->datetime('reversed_timestamp')->nullable();
            $table->foreignId('performed_by')->constrained('users');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['from_term_id', 'to_term_id']);
            $table->index('status');
        });

        Schema::create('rollover_mapping_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rollover_history_id')->constrained('rollover_histories')->onDelete('cascade');
            $table->string('table_name');
            $table->unsignedBigInteger('old_id');
            $table->unsignedBigInteger('new_id');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['rollover_history_id', 'table_name']);
            $table->index(['table_name', 'old_id', 'new_id']);
        });
    }

    public function down(){
        Schema::dropIfExists('rollover_mapping_data');
        Schema::dropIfExists('rollover_histories');
    }
};