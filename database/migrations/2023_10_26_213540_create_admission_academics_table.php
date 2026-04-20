<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{
    public function up(){
        Schema::create('admission_academics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admission_id')->index(); // Add index to admission_id column

            $table->string('science')->nullable();
            $table->string('mathematics')->nullable();
            $table->string('english')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('admission_id')->references('id')->on('admissions')->onDelete('cascade');
        });
    }

    public function down(){
        Schema::dropIfExists('admission_academics');
    }
};
