<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('asset_code')->unique()->comment('Barcode/RFID/Serial Number');
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->unsignedBigInteger('venue_id')->nullable();
            $table->string('status')->default('Available')->comment('Available, Assigned, In Maintenance, Disposed, etc.');
            $table->decimal('purchase_price', 12, 2)->nullable();
            $table->date('purchase_date')->nullable();
            $table->date('warranty_expiry')->nullable();
            $table->text('specifications')->nullable()->comment('JSON or text containing technical specs');
            $table->text('notes')->nullable();
            $table->string('make')->nullable()->comment('Manufacturer');
            $table->string('model')->nullable();
            $table->integer('expected_lifespan')->nullable()->comment('In months');
            $table->decimal('current_value', 12, 2)->nullable()->comment('After depreciation');
            $table->string('condition')->default('Good')->comment('New, Good, Fair, Poor');
            $table->string('invoice_number')->nullable();
            $table->string('image_path')->nullable();
            $table->json('custom_fields')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('category_id')->references('id')->on('asset_categories');
            $table->foreign('vendor_id')->references('id')->on('asset_vendors')->onDelete('set null');
            $table->foreign('venue_id')->references('id')->on('venues')->onDelete('set null'); // Assuming you have a venues table
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
