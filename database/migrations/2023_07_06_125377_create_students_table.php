```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{

    public function up(): void{
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('connect_id')->nullable()->index();
            $table->unsignedBigInteger('sponsor_id');
            $table->unsignedBigInteger('student_filter_id')->nullable();
            $table->unsignedBigInteger('student_type_id')->nullable();
            
            $table->string('first_name', 50);
            $table->string('last_name', 50);
            $table->string('exam_number', 50)->nullable();
            $table->string('photo_path')->nullable();
            $table->string('gender')->index();
            $table->date('date_of_birth');
            $table->string('email')->unique()->nullable();
            $table->string('nationality', 50)->index();
            $table->string('id_number', 20)->unique();
            $table->string('status')->default('Current')->index();
            $table->decimal('credit', 10, 2)->default(0); 
            $table->boolean('parent_is_staff')->nullable()->default(false);
            $table->year('year')->check('year >= 1990');
            $table->string('password',255);
            $table->rememberToken();

            $table->foreign('sponsor_id')->references('id')->on('sponsors')->onDelete('restrict')->onUpdate('cascade');    
            $table->foreign('student_filter_id')->references('id')->on('student_filters')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('student_type_id')->references('id')->on('student_types')->onDelete('set null')->onUpdate('cascade');

            $table->string('last_updated_by', 50)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['year', 'status']);
            $table->index(['last_name', 'first_name']);
            $table->index(['date_of_birth', 'nationality']);
        });
    }


    public function down(): void{
        Schema::dropIfExists('students');
    }
};
