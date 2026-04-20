<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

return new class extends Migration {

    public function up() {
        Schema::create('users_status', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        try {
            DB::table('users_status')->insert([
                ['name' => 'Current', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Left', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'To Join', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Deceased', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Retired', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'On Leave', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Deleted', 'created_at' => now(), 'updated_at' => now()],
            ]);
        } catch (QueryException $e) {
            Log::error("Error occurred while inserting data into 'users_status' table: " . $e->getMessage());
        }
    }

    public function down() {
        Schema::dropIfExists('users_status');
    }
};