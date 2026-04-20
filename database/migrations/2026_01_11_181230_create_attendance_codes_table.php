<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendance_codes', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('order')->default(0);
            $table->string('code', 10);
            $table->string('description', 100);
            $table->string('color', 7)->default('#6b7280'); // hex color
            $table->boolean('is_present')->default(false); // to distinguish present from absent codes
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique('code');
        });

        // Insert default attendance codes
        DB::table('attendance_codes')->insert([
            ['order' => 1, 'code' => '√', 'description' => 'Present', 'color' => '#10b981', 'is_present' => true, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['order' => 2, 'code' => 'A1', 'description' => 'Absent', 'color' => '#ef4444', 'is_present' => false, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['order' => 3, 'code' => 'A2', 'description' => 'Late', 'color' => '#f59e0b', 'is_present' => false, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['order' => 4, 'code' => 'A3', 'description' => 'Excused', 'color' => '#3b82f6', 'is_present' => false, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['order' => 5, 'code' => 'A4', 'description' => 'Sick', 'color' => '#6366f1', 'is_present' => false, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['order' => 6, 'code' => 'A5', 'description' => 'Authorized Leave', 'color' => '#06b6d4', 'is_present' => false, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['order' => 7, 'code' => 'A6', 'description' => 'Unauthorized Absence', 'color' => '#ec4899', 'is_present' => false, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attendance_codes');
    }
};
