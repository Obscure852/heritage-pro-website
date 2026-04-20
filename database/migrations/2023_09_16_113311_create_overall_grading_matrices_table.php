<?php

use App\Helpers\TermHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Grade;
use App\Models\OverallGradingMatrix;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up() {
        Schema::create('overall_grading_matrices', function (Blueprint $table) {
            $table->id();
            $table->integer('min_score')->nullable();
            $table->integer('max_score')->nullable();
            $table->string('grade')->nullable();
            $table->string('description')->nullable();
            $table->unsignedBigInteger('grade_id');
            $table->unsignedBigInteger('term_id');
            $table->year('year');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('term_id')->references('id')->on('terms')->onDelete('cascade');
            $table->foreign('grade_id')->references('id')->on('grades')->onDelete('cascade');
        });

        try {
            DB::transaction(function () {
                $currentTermId = TermHelper::getCurrentTerm()->id;
                $grades = Grade::all();

                foreach ($grades as $grade) {
                    $overallGradingMatrices = [
                        ['min_score' => 0, 'max_score' => 49, 'grade' => 'F', 'description' => 'Fail', 'grade_id' => $grade->id, 'term_id' => $currentTermId, 'year' => date('Y'), 'created_at' => now(), 'updated_at' => now()],
                        ['min_score' => 50, 'max_score' => 64, 'grade' => 'D', 'description' => 'Pass', 'grade_id' => $grade->id, 'term_id' => $currentTermId, 'year' => date('Y'), 'created_at' => now(), 'updated_at' => now()],
                        ['min_score' => 65, 'max_score' => 74, 'grade' => 'C', 'description' => 'Pass with Credit', 'grade_id' => $grade->id, 'term_id' => $currentTermId, 'year' => date('Y'), 'created_at' => now(), 'updated_at' => now()],
                        ['min_score' => 75, 'max_score' => 84, 'grade' => 'B', 'description' => 'Good', 'grade_id' => $grade->id, 'term_id' => $currentTermId, 'year' => date('Y'), 'created_at' => now(), 'updated_at' => now()],
                        ['min_score' => 85, 'max_score' => 100, 'grade' => 'A', 'description' => 'Excellent', 'grade_id' => $grade->id, 'term_id' => $currentTermId, 'year' => date('Y'), 'created_at' => now(), 'updated_at' => now()],
                    ];

                    OverallGradingMatrix::insert($overallGradingMatrices);
                }
            });
        } catch (\Exception $e) {
            Log::error('Error occurred while seeding overall grading matrices: ' . $e->getMessage());
        }
    }

    public function down() {
        Schema::dropIfExists('overall_grading_matrices');
    }
};