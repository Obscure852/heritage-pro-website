<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('request_activities')) {
            return;
        }

        Schema::create('request_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('requests')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('activity_type', 20);
            $table->string('subject')->nullable();
            $table->text('body');
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(['request_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_activities');
    }
};
