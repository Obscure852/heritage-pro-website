<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration {

    public function up()
    {
        Schema::create('area_of_work', function (Blueprint $table) {
            $table->id();
            $table->string('category');
            $table->string('name');
            $table->timestamps();

            $table->index('category');
            $table->index('name');
        });

        try {
            DB::table('area_of_work')->insert([
                ['category' => 'Academic', 'name' => 'Teaching', 'created_at' => now(), 'updated_at' => now()],
                ['category' => 'Administration', 'name' => 'Administration', 'created_at' => now(), 'updated_at' => now()],
                ['category' => 'Administration', 'name' => 'Maintenance', 'created_at' => now(), 'updated_at' => now()],
                ['category' => 'Administration', 'name' => 'Security', 'created_at' => now(), 'updated_at' => now()],
                ['category' => 'IT', 'name' => 'External Support', 'created_at' => now(), 'updated_at' => now()],
                ['category' => 'Library', 'name' => 'Librarian', 'created_at' => now(), 'updated_at' => now()],
            ]);
        } catch (\Exception $e) {
            Log::error("Error occurred while inserting data into 'area_of_work' table: " . $e->getMessage());
        }
    }

    public function down()
    {
        Schema::dropIfExists('area_of_work');
    }
};
