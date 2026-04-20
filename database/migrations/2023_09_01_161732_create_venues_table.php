<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

return new class extends Migration {

    public function up(){
        Schema::create('venues', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->string('type');
            $table->integer('capacity');
            $table->softDeletes();
            $table->timestamps();
        });

        $this->seedVenues();
    }

    public function down(){
        Schema::dropIfExists('venues');
    }

    private function seedVenues() {
        try {
            $venues = [
                ['name' => 'Lab 1', 'type' => 'Laboratory', 'capacity' => 32,'created_at' => now(),'updated_at' => now()],
                ['name' => 'Lab 2', 'type' => 'Laboratory', 'capacity' => 32,'created_at' => now(),'updated_at' => now()],
                ['name' => 'Lab 3', 'type' => 'Laboratory', 'capacity' => 32,'created_at' => now(),'updated_at' => now()],
                ['name' => 'Lab 4', 'type' => 'Laboratory', 'capacity' => 32,'created_at' => now(),'updated_at' => now()],
                ['name' => 'Lab 5', 'type' => 'Laboratory', 'capacity' => 32,'created_at' => now(),'updated_at' => now()],
                ['name' => 'Lab 6', 'type' => 'Laboratory', 'capacity' => 32,'created_at' => now(),'updated_at' => now()],
                ['name' => 'Lab 7', 'type' => 'Laboratory', 'capacity' => 32,'created_at' => now(),'updated_at' => now()],
                ['name' => 'Lab 8', 'type' => 'Laboratory', 'capacity' => 32,'created_at' => now(),'updated_at' => now()],
                ['name' => 'Lab 9', 'type' => 'Laboratory', 'capacity' => 32,'created_at' => now(),'updated_at' => now()],
                ['name' => 'Room 1', 'type' => 'Classroom', 'capacity' => 32,'created_at' => now(),'updated_at' => now()],
                ['name' => 'Room 2', 'type' => 'Classroom', 'capacity' => 32,'created_at' => now(),'updated_at' => now()],
                ['name' => 'Room 3', 'type' => 'Classroom', 'capacity' => 32,'created_at' => now(),'updated_at' => now()],
                ['name' => 'Room 4', 'type' => 'Classroom', 'capacity' => 32,'created_at' => now(),'updated_at' => now()],
                ['name' => 'Room 5', 'type' => 'Classroom', 'capacity' => 32,'created_at' => now(),'updated_at' => now()],
                ['name' => 'Room 6', 'type' => 'Classroom', 'capacity' => 32,'created_at' => now(),'updated_at' => now()],
                ['name' => 'Room 7', 'type' => 'Classroom', 'capacity' => 32,'created_at' => now(),'updated_at' => now()],
                ['name' => 'Room 8', 'type' => 'Classroom', 'capacity' => 32,'created_at' => now(),'updated_at' => now()],
                ['name' => 'Room 9', 'type' => 'Classroom', 'capacity' => 32,'created_at' => now(),'updated_at' => now()],
                ['name' => 'Room 10', 'type' => 'Classroom', 'capacity' => 32,'created_at' => now(),'updated_at' => now()],
                ['name' => 'Room 11', 'type' => 'Classroom', 'capacity' => 32,'created_at' => now(),'updated_at' => now()],
                ['name' => 'Room 12', 'type' => 'Classroom', 'capacity' => 32,'created_at' => now(),'updated_at' => now()],
                ['name' => 'Room 13', 'type' => 'Classroom', 'capacity' => 32,'created_at' => now(),'updated_at' => now()],
                ['name' => 'Room 14', 'type' => 'Classroom', 'capacity' => 32,'created_at' => now(),'updated_at' => now()],
                ['name' => 'Room 15', 'type' => 'Classroom', 'capacity' => 32,'created_at' => now(),'updated_at' => now()],
                ['name' => 'Room 16', 'type' => 'Classroom', 'capacity' => 32,'created_at' => now(),'updated_at' => now()],
                ['name' => 'Room 17', 'type' => 'Classroom', 'capacity' => 32,'created_at' => now(),'updated_at' => now()],
                ['name' => 'Room 18', 'type' => 'Classroom', 'capacity' => 32,'created_at' => now(),'updated_at' => now()],
                ['name' => 'Room 19', 'type' => 'Classroom', 'capacity' => 32,'created_at' => now(),'updated_at' => now()],
                ['name' => 'Room 20', 'type' => 'Classroom', 'capacity' => 32,'created_at' => now(),'updated_at' => now()],
                ['name' => 'Room 21', 'type' => 'Classroom', 'capacity' => 32,'created_at' => now(),'updated_at' => now()],
                ['name' => 'Room 22', 'type' => 'Classroom', 'capacity' => 32,'created_at' => now(),'updated_at' => now()],
                ['name' => 'Room 23', 'type' => 'Classroom', 'capacity' => 32,'created_at' => now(),'updated_at' => now()],
                ['name' => 'Room 24', 'type' => 'Classroom', 'capacity' => 32,'created_at' => now(),'updated_at' => now()],
                ['name' => 'Room 25', 'type' => 'Classroom', 'capacity' => 32,'created_at' => now(),'updated_at' => now()],
            ];
            DB::table('venues')->insert($venues);
        } catch (\Exception $e) {
            Log::error('Error occurred while seeding venues: ' . $e->getMessage());
        }
    }
};
