<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('activities')) {
            return;
        }

        Schema::create('activities', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('code', 50);
            $table->string('category', 50);
            $table->string('delivery_mode', 30);
            $table->string('participation_mode', 30);
            $table->string('result_mode', 30);
            $table->text('description')->nullable();
            $table->string('default_location')->nullable();
            $table->unsignedInteger('capacity')->nullable();
            $table->string('gender_policy', 20)->nullable();
            $table->boolean('attendance_required')->default(true);
            $table->boolean('allow_house_linkage')->default(false);
            $table->unsignedBigInteger('fee_type_id')->nullable();
            $table->decimal('default_fee_amount', 10, 2)->nullable();
            $table->string('status', 20)->default('draft');
            $table->unsignedBigInteger('term_id');
            $table->year('year');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('fee_type_id')->references('id')->on('fee_types')->nullOnDelete();
            $table->foreign('term_id')->references('id')->on('terms')->restrictOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();

            $table->unique(['code', 'year']);
            $table->index(['term_id', 'year']);
            $table->index(['status', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
