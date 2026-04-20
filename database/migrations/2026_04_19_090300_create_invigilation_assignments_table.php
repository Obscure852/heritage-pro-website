<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('invigilation_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('session_room_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedTinyInteger('assignment_order')->default(1);
            $table->string('assignment_source', 20)->default('manual');
            $table->boolean('locked')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('session_room_id')->references('id')->on('invigilation_session_rooms')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();

            $table->unique(['session_room_id', 'assignment_order'], 'invigilation_room_assignment_order_unique');
            $table->index(['user_id', 'assignment_source']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invigilation_assignments');
    }
};
