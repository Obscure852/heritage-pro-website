<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{

    public function up(){
        Schema::create('emails', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('term_id');
            $table->unsignedBigInteger('sender_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('sponsor_id')->nullable();
            $table->string('receiver_type');
            $table->string('subject');
            $table->text('body');
            $table->string('attachment_path')->nullable();
            $table->string('status');
            $table->text('error_message')->nullable();
            $table->integer('num_of_recipients');
            $table->string('type');
            $table->json('filters')->nullable();
            $table->timestamps();

            $table->index('status', 'idx_emails_status');
            $table->index('type', 'idx_emails_type');
            $table->index('term_id', 'idx_emails_term_id');
            $table->index('sender_id', 'idx_emails_sender_id');
            $table->index('created_at', 'idx_emails_created_at');
            $table->index(['term_id', 'status'], 'idx_emails_term_status');
            $table->index(['sender_id', 'created_at'], 'idx_emails_sender_created');

            $table->foreign('term_id')->references('id')->on('terms')->onDelete('cascade');
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('sponsor_id')->references('id')->on('sponsors')->onDelete('cascade');
        });
    }

    public function down(){
        Schema::dropIfExists('emails');
    }
};
