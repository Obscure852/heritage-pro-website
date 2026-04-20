<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration{
    public function up(){
        Schema::create('overall_points_matrix', function (Blueprint $table) {
            $table->id();
            $table->string('academic_year')->nullable();
            $table->year('year')->default(now()->year)->nullable();
            $table->integer('min')->nullable();
            $table->integer('max')->nullable();
            $table->string('grade')->nullable();
            $table->timestamps();

            $table->index('grade');
        });


        try {
            $academicYears = ['F1', 'F2', 'F3'];
            $gradesBoundaries = [
                ['min' => 63, 'max' => 100, 'grade' => 'Merit'],
                ['min' => 55, 'max' => 62,   'grade' => 'A'],
                ['min' => 41, 'max' => 54,   'grade' => 'B'],
                ['min' => 27, 'max' => 40,   'grade' => 'C'],
                ['min' => 13, 'max' => 26,   'grade' => 'D'],
                ['min' => 7,  'max' => 12,   'grade' => 'E'],
                ['min' => 0,  'max' => 6,    'grade' => 'U'],
            ];

            foreach ($academicYears as $year) {
                foreach ($gradesBoundaries as $boundary) {
                    DB::table('overall_points_matrix')->insert([
                        'academic_year' => $year,
                        'min' => $boundary['min'],
                        'max' => $boundary['max'],
                        'grade' => $boundary['grade'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error("Error occurred while inserting data into 'overall_points_matrix' table: " . $e->getMessage());
        }
    }

    public function down(){
        Schema::dropIfExists('overall_points_matrix');
    }
};
