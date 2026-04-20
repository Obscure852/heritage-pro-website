<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{
    
    public function up(){
        
        Schema::create('regional_access_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('school_code', 50);
            $table->string('regional_officer_id', 50)->nullable();
            $table->string('regional_officer_name', 255);
            $table->string('regional_officer_email', 255);
            $table->enum('access_type', ['regional_office', 'ministry']);
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('accessed_at');
            $table->timestamps();
            
            $table->index(['school_code', 'accessed_at']);
            $table->index(['regional_officer_id', 'accessed_at']);
            $table->index(['user_id', 'accessed_at']);
        });

        if (Schema::hasTable('personal_access_tokens')) {
            Schema::table('personal_access_tokens', function (Blueprint $table) {
                if (!Schema::hasColumn('personal_access_tokens', 'expires_at')) {
                    $table->timestamp('expires_at')->nullable()->after('last_used_at');
                }
            });
        }
    }

    public function down(){
        if (Schema::hasTable('personal_access_tokens')) {
            Schema::table('personal_access_tokens', function (Blueprint $table) {
                if (Schema::hasColumn('personal_access_tokens', 'expires_at')) {
                    $table->dropColumn('expires_at');
                }
            });
        }
        
        Schema::dropIfExists('regional_access_logs');
    }
};
