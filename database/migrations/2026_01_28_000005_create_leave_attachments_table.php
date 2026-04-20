<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{
    public function up(){
        if (Schema::hasTable('leave_attachments')) {
            return;
        }
        Schema::create('leave_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('leave_request_id');
            $table->string('file_name');
            $table->string('file_path');
            $table->integer('file_size');
            $table->string('mime_type');
            $table->unsignedBigInteger('uploaded_by');
            $table->timestamp('created_at')->nullable();

            $table->foreign('leave_request_id')->references('id')->on('leave_requests')->onDelete('cascade');
            $table->foreign('uploaded_by')->references('id')->on('users')->onDelete('cascade');

            $table->index('leave_request_id');
            $table->index('uploaded_by');
        });
    }

    public function down(){
        Schema::dropIfExists('leave_attachments');
    }
};
