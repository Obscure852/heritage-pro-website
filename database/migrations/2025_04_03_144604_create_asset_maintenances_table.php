<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{
    public function up(): void
    {
        Schema::create('asset_maintenances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('asset_id');
            $table->string('maintenance_type')->comment('Preventive, Corrective, Upgrade');
            $table->date('maintenance_date');
            $table->date('next_maintenance_date')->nullable();
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->decimal('cost', 12, 2)->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('Scheduled')->comment('Scheduled, In Progress, Completed, Cancelled');
            $table->text('results')->nullable();
            $table->unsignedBigInteger('performed_by')->nullable();
            $table->timestamps();
            
            $table->foreign('asset_id')->references('id')->on('assets');
            $table->foreign('vendor_id')->references('id')->on('asset_vendors')->onDelete('set null');
            $table->foreign('performed_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_maintenances');
    }
};
