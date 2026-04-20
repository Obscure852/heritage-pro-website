<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{

    public function up(){
        Schema::create('s_m_s_api_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('value');
            $table->string('category')->nullable();
            $table->string('type')->default('string');
            $table->text('description')->nullable();
            $table->string('display_name')->nullable();
            $table->text('validation_rules')->nullable();
            $table->boolean('is_editable')->default(true);
            $table->integer('display_order')->default(0);
            $table->timestamps();

            $table->index('category', 'idx_sms_api_settings_category');
            $table->index('display_order', 'idx_sms_api_settings_display_order');
        });
    }

    public function down(){
        Schema::dropIfExists('s_m_s_api_settings');
    }
};
