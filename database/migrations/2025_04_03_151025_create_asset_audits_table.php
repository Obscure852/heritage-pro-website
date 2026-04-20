<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{

    public function up(): void{
        Schema::create('asset_audits', function (Blueprint $table) {
            $table->id();
            $table->string('audit_code')->unique();
            $table->date('audit_date');
            $table->date('next_audit_date')->nullable();
            $table->string('status')->default('Pending')->comment('Pending, In Progress, Completed');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('conducted_by');
            $table->timestamps();
            
            $table->foreign('conducted_by')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_audits');
    }
};
