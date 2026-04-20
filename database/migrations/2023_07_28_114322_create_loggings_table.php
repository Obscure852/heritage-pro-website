<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up() {
        Schema::create('loggings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('location');
            $table->string('ip_address');
            $table->text('user_agent');
            $table->string('url');
            $table->string('method');
            $table->text('input')->nullable();
            $table->text('changes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('logging_archives', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('ip_address');
            $table->text('user_agent');
            $table->string('url');
            $table->string('method');
            $table->text('input')->nullable();
            $table->text('changes')->nullable();
            $table->timestamp('archived_at')->nullable(); // Timestamp when archived
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down() {
        Schema::dropIfExists('loggings');
        Schema::dropIfExists('logging_archives');
    }
};
