<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('invigilation_session_rooms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('session_id');
            $table->unsignedBigInteger('venue_id');
            $table->string('source_type', 20)->default('manual');
            $table->unsignedBigInteger('klass_subject_id')->nullable();
            $table->unsignedBigInteger('optional_subject_id')->nullable();
            $table->string('group_label')->nullable();
            $table->unsignedInteger('candidate_count')->default(0);
            $table->unsignedTinyInteger('required_invigilators')->default(1);
            $table->timestamps();

            $table->foreign('session_id')->references('id')->on('invigilation_sessions')->cascadeOnDelete();
            $table->foreign('venue_id')->references('id')->on('venues')->cascadeOnDelete();
            $table->foreign('klass_subject_id')->references('id')->on('klass_subject')->nullOnDelete();
            $table->foreign('optional_subject_id')->references('id')->on('optional_subjects')->nullOnDelete();

            $table->index(['session_id', 'venue_id']);
            $table->index(['source_type', 'klass_subject_id']);
            $table->index(['source_type', 'optional_subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invigilation_session_rooms');
    }
};
