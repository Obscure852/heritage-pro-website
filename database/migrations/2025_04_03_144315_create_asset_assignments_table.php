<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{

    public function up(): void
    {
        Schema::create('asset_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('asset_id');
            $table->morphs('assignable');
            $table->date('assigned_date');
            $table->date('expected_return_date')->nullable();
            $table->date('actual_return_date')->nullable();
            $table->string('status')->default('Assigned')->comment('Assigned, Returned, Overdue');
            $table->text('assignment_notes')->nullable();
            $table->text('return_notes')->nullable();
            $table->string('condition_on_assignment')->default('Good');
            $table->string('condition_on_return')->nullable();
            $table->unsignedBigInteger('assigned_by');
            $table->unsignedBigInteger('received_by')->nullable();
            $table->timestamps();
            
            $table->foreign('asset_id')->references('id')->on('assets');
            $table->foreign('assigned_by')->references('id')->on('users');
            $table->foreign('received_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_assignments');
    }
};
