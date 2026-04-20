<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{
    public function up(){
        if (Schema::hasTable('leave_settings')) {
            return;
        }
        Schema::create('leave_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->json('value');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');

            $table->index('key');
        });
    }

    public function down(){
        Schema::dropIfExists('leave_settings');
    }
};
