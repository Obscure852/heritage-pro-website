<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApiAccessLogsTable extends Migration{

    public function up(){
        Schema::create('api_access_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action', 100);
            $table->string('resource_type', 50);
            $table->unsignedBigInteger('resource_id')->nullable();
            $table->string('ip_address', 45);
            $table->text('user_agent')->nullable();
            $table->json('request_data')->nullable();
            $table->timestamp('accessed_at');
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('action');
            $table->index('resource_type');
            $table->index(['resource_type', 'resource_id']);
            $table->index('accessed_at');
            
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });
    }

    public function down(){
        Schema::dropIfExists('api_access_logs');
    }
}
