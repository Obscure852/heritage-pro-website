<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{

    public function up(){
        Schema::create('book_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('copy_id')->constrained()->onDelete('cascade');
            $table->foreignId('grade_id')->constrained()->onDelete('cascade');
            $table->string('accession_number');
            $table->date('allocation_date');
            $table->date('due_date');
            $table->date('return_date')->nullable();
            $table->enum('condition_on_allocation', ['New', 'Good', 'Fair', 'Poor'])->default('Good');
            $table->enum('condition_on_return', ['Good', 'Fair', 'Poor', 'Damaged', 'Lost'])->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(){
        Schema::dropIfExists('book_allocations');
    }
};
