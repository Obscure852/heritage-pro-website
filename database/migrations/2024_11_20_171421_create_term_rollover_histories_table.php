<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{
    
    public function up(): void{
        Schema::create('term_rollover_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_term_id')->constrained('terms');
            $table->foreignId('to_term_id')->constrained('terms');
            $table->foreignId('performed_by')->constrained('users');
            $table->json('mappings')->nullable();
            $table->enum('status', ['in-progress', 'completed', 'reversed'])->default('completed');
            $table->timestamp('reversed_at')->nullable();
            $table->timestamps();
            
            $table->index(['from_term_id', 'to_term_id']);
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void{
        Schema::dropIfExists('term_rollover_histories');
    }
};