<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('sponsors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sponsor_filter_id')->nullable();
            $table->bigInteger('connect_id');

            $table->string('title')->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique()->nullable();
            $table->string('gender')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('nationality')->nullable();
            $table->string('relation')->nullable();
            $table->string('status')->nullable();
            $table->string('id_number')->nullable();
            $table->string('phone')->nullable();
            $table->string('profession')->nullable();
            $table->string('work_place')->nullable();
            $table->string('telephone')->nullable();
            
            $table->string('password')->nullable();
            $table->rememberToken();
            $table->string('last_updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('sponsor_filter_id')->references('id')->on('sponsor_filters')->onDelete('set null')->onUpdate('cascade');

            $table->index('first_name');
            $table->index('last_name');
            $table->index(['first_name', 'last_name']);
            $table->index('connect_id');
            $table->index('id_number');
            $table->index('phone');
            $table->index('sponsor_filter_id');
            $table->index('last_updated_by');
        });
    }

    public function down() {
        Schema::dropIfExists('sponsors');
    }
};
