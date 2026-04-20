<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration {

    public function up()
    {
        Schema::create('user_positions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();

            $table->index('name');
        });

        try {
            DB::table('user_positions')->insert([
                ['name' => 'School Head', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Deputy School Head', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'HOD', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Teacher', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Assistant Teacher', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Teacher Aide', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Scribe', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Secretary', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Senior Teacher 1', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Senior Teacher 2', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'IT Officer', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'External Support', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Admin Officer', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Supplies Officer', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Procurement Officer', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Bursar', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Assistant Teacher', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Internship', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Temporary Teacher', 'created_at' => now(), 'updated_at' => now()],
            ]);
        } catch (\Exception $e) {
            Log::error("Error occurred while inserting data into 'user_positions' table: " . $e->getMessage());
        }
    }

    public function down()
    {
        Schema::dropIfExists('user_positions');
    }
};
