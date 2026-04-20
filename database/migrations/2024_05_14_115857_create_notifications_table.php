<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration{

    public function up(){
        
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('term_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('body');
            $table->boolean('is_general')->nullable()->default(false);
            $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('set null');
            $table->foreignId('filter_id')->nullable()->constrained('sponsor_filters')->onDelete('set null');
            $table->string('area_of_work')->nullable();
            $table->boolean('allow_comments')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('term_id');
            $table->index('title');
        });
    }

    public function down(){
        Schema::dropIfExists('notifications');
    }
    
};
