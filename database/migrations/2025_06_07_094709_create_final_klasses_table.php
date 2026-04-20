<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{

    public function up(){
        Schema::create('final_klasses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('original_klass_id');
            $table->string('name');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('graduation_term_id');
            $table->unsignedBigInteger('grade_id');
            $table->string('type')->nullable();
            $table->year('graduation_year');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['graduation_term_id', 'graduation_year']);
            $table->index('original_klass_id');
        });
    }

    public function down(){
        Schema::dropIfExists('final_klasses');
    }
};
