<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Helpers\TermHelper;
use App\Models\SchoolSetup;
use App\Models\Term;
use App\Support\AcademicStructureRegistry;

return new class extends Migration{
    public function up(){
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->integer('sequence');
            $table->string('name');
            $table->string('promotion');
            $table->string('description');
            $table->string('level');
            $table->boolean('active');
            $table->unsignedBigInteger('term_id');
            $table->year('year');

            $table->foreign('term_id')->references('id')->on('terms')->onDelete('cascade');
            $table->softDeletes();
            $table->timestamps();

            $table->index('term_id');
            $table->index('year');
            $table->index('level');
        });

        try{
            $currentTerm = TermHelper::getCurrentTerm();
            if (!$currentTerm) {
                throw new \Exception('No current term found.');
            }

            $previousTermsInYear = Term::where('year', $currentTerm->year)->where('term', '<', $currentTerm->term)->where('closed', 0)->get();
            if ($previousTermsInYear->isNotEmpty()) {
                Log::info("Closing {$previousTermsInYear->count()} previous terms in year {$currentTerm->year} before creating grades for term {$currentTerm->term}");
                
                foreach ($previousTermsInYear as $term) {
                    $term->closed = 1;
                    $term->extension_days = 0;
                    $term->save();
                    
                    Log::info("Closed term {$term->term} of year {$term->year} (ID: {$term->id})");
                }
            }

            $schoolType = SchoolSetup::normalizeType(SchoolSetup::value('type'));
            $gradeRows = collect(AcademicStructureRegistry::gradeDefinitionsForMode($schoolType))
                ->map(function (array $grade) use ($currentTerm) {
                    return [
                        'sequence' => $grade['sequence'],
                        'name' => $grade['name'],
                        'promotion' => $grade['promotion'],
                        'description' => $grade['description'],
                        'level' => $grade['level'],
                        'active' => true,
                        'term_id' => $currentTerm->id,
                        'year' => $currentTerm->year,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                })
                ->all();

            if (!empty($gradeRows)) {
                DB::table('grades')->insert($gradeRows);
            }

            Log::info("Successfully created grades for term {$currentTerm->term} of year {$currentTerm->year}");

        }catch(\Exception $e){
            Log::error('Failed to insert grades: ' . $e->getMessage());
        }
    }

    public function down(){
        Schema::dropIfExists('grades');
    }
};
