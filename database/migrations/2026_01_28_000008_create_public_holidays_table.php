<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{
    public function up(){
        if (Schema::hasTable('public_holidays')) {
            return;
        }
        Schema::create('public_holidays', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('date');
            $table->boolean('is_recurring')->default(false);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('date');
            $table->index('is_active');
            $table->index(['date', 'is_active']);
        });
    }

    public function down(){
        Schema::dropIfExists('public_holidays');
    }
};
