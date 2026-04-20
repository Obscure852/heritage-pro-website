<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{
    
    public function up(){
        Schema::create('sms_job_tracking', function (Blueprint $table) {
            $table->id();
            $table->string('job_id')->unique()->index();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('term_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->enum('recipient_type', ['sponsor', 'user']);
            $table->text('message');
            $table->json('filters')->nullable();
            $table->integer('total_recipients')->default(0);
            $table->integer('sent_count')->default(0);
            $table->integer('failed_count')->default(0);
            $table->integer('percentage')->default(0);
            $table->decimal('total_cost', 10, 2)->default(0.00);
            $table->integer('sms_units_used')->default(0);
            $table->text('status_message')->nullable();
            $table->json('errors')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            
            $table->index('status');
            $table->index('created_at');
            $table->index(['user_id', 'status']);
        });
    }

    public function down(){
        Schema::dropIfExists('sms_job_tracking');
    }
};