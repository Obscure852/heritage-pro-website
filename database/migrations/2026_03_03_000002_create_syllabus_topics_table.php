<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the syllabus_topics table — ordered topics within a syllabus.
 *
 * Each topic belongs to a syllabus and has a sequence number for ordering.
 * Scheme of work entries can reference a topic to indicate which part of the
 * curriculum is being covered in a given week.
 *
 * Requirements: FOUN-01
 */
return new class extends Migration {
    public function up(): void {
        Schema::create('syllabus_topics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('syllabus_id')->index();
            $table->unsignedSmallInteger('sequence');
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('suggested_weeks')->nullable();
            $table->timestamps();

            $table->foreign('syllabus_id')
                ->references('id')
                ->on('syllabi')
                ->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('syllabus_topics');
    }
};
