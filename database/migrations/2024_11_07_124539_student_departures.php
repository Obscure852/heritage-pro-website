<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{

    public function up(){
        Schema::create('student_departures', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->date('last_day_of_attendance');
            $table->enum('reason_for_leaving', [
                'Graduation',
                'Transfer to another school',
                'Relocation',
                'Withdrawal',
                'Dropout - Pregnancy',
                'Illness',
                'Expulsion',
                'Other'
            ]);
            $table->string('reason_for_leaving_other', 255)->nullable();
            $table->string('new_school_name', 255)->nullable();
            $table->string('new_school_contact_number', 20)->nullable();
            $table->boolean('outstanding_fees')->default(false);
            $table->boolean('property_returned')->default(false);
            $table->year('year')->check('year >= 2000');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('processed_by')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('student_id');
            $table->index('last_day_of_attendance');
            $table->index('reason_for_leaving');
            $table->index('year');
            $table->index(['student_id', 'year']);
            $table->index(['student_id', 'last_day_of_attendance']);

            $table->foreign('student_id')->references('id')->on('students')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('processed_by')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
        });
    }


    public function down(): void{
        Schema::dropIfExists('student_departures');
    }
};
