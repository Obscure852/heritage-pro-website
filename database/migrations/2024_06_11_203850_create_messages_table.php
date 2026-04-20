<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{
    
    public function up(){
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('term_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('author')->nullable();
            $table->unsignedBigInteger('sponsor_id')->nullable();
            $table->text('body');
            $table->integer('sms_count');
            $table->string('twilio_sid')->nullable();
            $table->string('type')->default('direct');
            $table->integer('num_recipients')->nullable();
            $table->string('status')->nullable();
            $table->decimal('price', 8, 4)->nullable();
            $table->decimal('price_bwp', 8, 4)->nullable();
            $table->string('price_unit')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('sponsor_id')->references('id')->on('sponsors')->onDelete('cascade');
            $table->foreign('author')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('term_id')->references('id')->on('terms')->onDelete('cascade');

            $table->index('user_id');
            $table->index('term_id');
            $table->index('sponsor_id');
            $table->index('price');
            $table->index('status');
            $table->index('price_bwp');
        });
    }

    public function down(){
        Schema::dropIfExists('messages');
    }
};
