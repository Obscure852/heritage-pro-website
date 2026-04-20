<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{

    public function up(): void
    {
        Schema::create('asset_disposals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('asset_id');
            $table->date('disposal_date');
            $table->string('disposal_method')->comment('Sold, Scrapped, Donated, Recycled');
            $table->decimal('disposal_amount', 12, 2)->nullable()->comment('If sold');
            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            $table->string('recipient')->nullable()->comment('Person or organization receiving the asset');
            $table->unsignedBigInteger('authorized_by');
            $table->timestamps();
            
            $table->foreign('asset_id')->references('id')->on('assets');
            $table->foreign('authorized_by')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_disposals');
    }
};
