<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOnlineApplicationAttachmentsTable extends Migration{
    public function up(){
        Schema::create('online_application_attachments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('admission_id');
            $table->string('attachment_type');
            $table->string('file_path');
            $table->timestamps();

            $table->foreign('admission_id')->references('id')->on('admissions')->onDelete('cascade');
        });
    }

    public function down(){
        Schema::dropIfExists('online_application_attachments');
    }
}