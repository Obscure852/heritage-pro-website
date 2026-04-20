<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateManualAttendanceEntriesTable extends Migration{
    
    public function up(){
        Schema::create('manual_attendance_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('term_id')->constrained();
            $table->string('days_absent')->default(0);
            $table->string('school_fees_owing')->default(0.00);
            $table->text('other_info')->nullable();
            $table->timestamps();
        });
    }

    public function down(){
        Schema::dropIfExists('manual_attendance_entries');
    }
}