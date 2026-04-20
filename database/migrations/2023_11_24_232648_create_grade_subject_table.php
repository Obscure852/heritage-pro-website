<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Helpers\TermHelper;
use App\Models\SchoolSetup;
use App\Support\AcademicStructureRegistry;
use Illuminate\Support\Facades\Log;

return new class extends Migration {
    public function up() {
        Schema::create('grade_subject', function (Blueprint $table) {
            $table->id();
            $table->integer('sequence')->default(0);
            $table->unsignedBigInteger('grade_id');
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('department_id');
            $table->unsignedBigInteger('term_id');
            $table->year('year');
            $table->string('type');
            $table->boolean('mandatory');
            $table->boolean('active')->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('grade_id')->references('id')->on('grades')->onDelete('cascade');
            $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->foreign('term_id')->references('id')->on('terms')->onDelete('cascade');

            $table->index('grade_id');
            $table->index('subject_id');
            $table->index('department_id');
            $table->index('term_id');
            $table->index('year');
            $table->index('type');
            $table->index('mandatory');
            $table->index('active');
            $table->index('sequence');
        });

        Schema::create('components', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('term_id');
            $table->unsignedBigInteger('grade_subject_id');
            $table->unsignedBigInteger('grade_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->foreign('term_id')->references('id')->on('terms');
            $table->foreign('grade_subject_id')->references('id')->on('grade_subject');
            $table->foreign('grade_id')->references('id')->on('grades');

            $table->index('term_id');
            $table->index('grade_subject_id');
            $table->index('grade_id');
            $table->index('name');
        });

        try {
            $school_type = SchoolSetup::normalizeType(SchoolSetup::value('type'));
            Log::info('School type:', ['type' => $school_type]);
            $this->populateGradeSubjectsForMode($school_type);
        } catch (\Exception $e) {
            Log::error('Error populating subjects: ' . $e->getMessage());
            throw $e;
        }
    }

    public function down() {
        Schema::dropIfExists('components');
        Schema::dropIfExists('grade_subject');
    }

    private function populateGradeSubjectsForMode(string $schoolType): void {
        $currentTerm = TermHelper::getCurrentTerm();
        if (!$currentTerm) {
            throw new \RuntimeException('No current term found while populating grade subjects.');
        }

        DB::transaction(function () use ($currentTerm, $schoolType) {
            $departments = DB::table('departments')->pluck('id', 'name');
            $fallbackDepartmentId = $departments->get('Administration') ?? $departments->first();
            $gradeMap = DB::table('grades')
                ->where('term_id', $currentTerm->id)
                ->get(['id', 'name', 'level'])
                ->keyBy(fn ($grade) => "{$grade->name}|{$grade->level}");
            $subjectMap = DB::table('subjects')
                ->get(['id', 'level', 'abbrev', 'name', 'canonical_key', 'department'])
                ->keyBy(function ($subject) {
                    $canonicalKey = $subject->canonical_key ?: AcademicStructureRegistry::canonicalKeyFor(
                        $subject->level,
                        $subject->abbrev,
                        $subject->name
                    );

                    return "{$subject->level}|{$canonicalKey}";
                });
            $subjectComponents = AcademicStructureRegistry::preschoolComponents();

            foreach (AcademicStructureRegistry::gradeSubjectDefinitionsForMode($schoolType) as $definition) {
                $grade = $gradeMap->get("{$definition['grade_name']}|{$definition['grade_level']}");
                $subject = $subjectMap->get("{$definition['subject_level']}|{$definition['canonical_key']}");

                if (!$grade || !$subject || !$fallbackDepartmentId) {
                    Log::warning('Skipping grade subject definition during migration seeding.', [
                        'grade_key' => "{$definition['grade_name']}|{$definition['grade_level']}",
                        'subject_key' => "{$definition['subject_level']}|{$definition['canonical_key']}",
                    ]);
                    continue;
                }

                $gradeSubjectId = DB::table('grade_subject')->insertGetId([
                        'grade_id' => $grade->id,
                        'subject_id' => $subject->id,
                        'department_id' => $departments->get($subject->department ?? '') ?? $fallbackDepartmentId,
                        'term_id' => $currentTerm->id,
                        'year' => $currentTerm->year,
                        'type' => (string) $definition['type'],
                        'mandatory' => (bool) $definition['mandatory'],
                        'active' => true,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                if (!empty($definition['components'])) {
                    foreach ($subjectComponents[$definition['canonical_key']] ?? [] as $componentName) {
                        DB::table('components')->insert([
                            'term_id' => $currentTerm->id,
                            'grade_subject_id' => $gradeSubjectId,
                            'grade_id' => $grade->id,
                            'name' => $componentName,
                            'description' => '',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        });
    }
};
