<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{

    public function up(){
        Schema::create('notification_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notification_id')->constrained()->onDelete('cascade');
            $table->string('original_name');
            $table->string('file_path');
            $table->string('file_type');
            $table->timestamps();

            $table->index('notification_id');
            $table->index('original_name');
            $table->index('file_path');
            $table->index('file_type');
        });
    }


    public function down(){
        Schema::dropIfExists('notification_attachments');
    }
};
