<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{

    public function up(): void
    {
        Schema::create('asset_audit_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('audit_id');
            $table->unsignedBigInteger('asset_id');
            $table->boolean('is_present')->default(false);
            $table->string('condition')->nullable();
            $table->boolean('needs_maintenance')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->foreign('audit_id')->references('id')->on('asset_audits')->onDelete('cascade');
            $table->foreign('asset_id')->references('id')->on('assets');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_audit_items');
    }
};
