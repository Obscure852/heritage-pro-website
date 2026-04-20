<?php

namespace App\Jobs;

use App\Helpers\AssessmentHelper;
use App\Models\GradingScale;
use App\Models\Klass;
use App\Models\KlassSubject;
use App\Models\OptionalSubject;
use App\Models\StudentTest;
use App\Models\SubjectComment;
use App\Models\Term;
use App\Models\Test;
use App\Services\RemarkGenerationService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Models\User;

class RecalculateGrades implements ShouldQueue{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600;
    public $tries = 1;

    protected int $classId;
    protected string $subjectType;
    protected int $selectedTermId;
    protected ?int $userId;
    protected string $jobId;

    public function __construct(int $classId, string $subjectType, int $selectedTermId, ?int $userId = null){
        $this->classId = $classId;
        $this->subjectType = $subjectType;
        $this->selectedTermId = $selectedTermId;
        $this->userId = $userId;
        $this->jobId = uniqid('recalc_', true);
    }

    public function handle(): void{
        // Increase memory limit for large datasets
        ini_set('memory_limit', '1024M');

        // Create unique lock key for this grade/term/subject combination
        $lockKey = "recalculate_grades_{$this->classId}_{$this->subjectType}_{$this->selectedTermId}";

        // Try to acquire lock (expires in 1 hour)
        $lock = Cache::lock($lockKey, 3600);

        if (!$lock->get()) {
            Log::warning("Recalculation already in progress for this combination", [
                'class_id' => $this->classId,
                'subject_type' => $this->subjectType,
                'term_id' => $this->selectedTermId
            ]);

            $this->updateProgress(0, 'failed', 'A recalculation is already in progress for this combination.');
            return;
        }

        try {
            Log::info("Starting grade recalculation job", [
                'job_id' => $this->jobId,
                'class_id' => $this->classId,
                'subject_type' => $this->subjectType,
                'term_id' => $this->selectedTermId,
                'user_id' => $this->userId
            ]);

            $this->updateProgress(0, 'processing', 'Initializing recalculation...');

            $klass = Klass::find($this->classId);
            if (!$klass) {
                throw new Exception("Class with ID {$this->classId} not found.");
            }

            $selectedTerm = Term::find($this->selectedTermId);
            if (!$selectedTerm) {
                throw new Exception("Selected term with ID {$this->selectedTermId} not found.");
            }

            $gradeId = $klass->grade_id;
            $termId = $this->selectedTermId;
            $year = $selectedTerm->year;

            $this->updateProgress(5, 'processing', 'Fetching subjects...');
            $gradeSubjectIds = $this->getGradeSubjectIdsByType($gradeId, $termId, $this->subjectType);

            if ($gradeSubjectIds->isEmpty()) {
                Log::info("No subjects to process for grade {$gradeId} in term {$termId}. Job finished.");
                $this->updateProgress(100, 'completed', 'No subjects found to process.');
                $lock->release();
                return;
            }

            $totalSubjects = $gradeSubjectIds->count();
            $processedStudents = [];
            $processedTests = 0;
            $subjectsProcessed = 0;

            $this->updateProgress(10, 'processing', "Processing {$totalSubjects} subjects...");

            foreach ($gradeSubjectIds as $index => $gradeSubjectId) {
                $subjectsProcessed++;

                // Calculate progress (10% to 80% for subject processing)
                $progress = 10 + (($subjectsProcessed / $totalSubjects) * 70);
                $this->updateProgress(
                    round($progress),
                    'processing',
                    "Processing subject {$subjectsProcessed}/{$totalSubjects}..."
                );

                $result = $this->processSubjectGrades($gradeSubjectId, $termId, $year);
                $processedStudents = array_merge($processedStudents, $result['students']);
                $processedTests += $result['tests'];

                // Clear memory periodically
                if ($subjectsProcessed % 10 === 0) {
                    gc_collect_cycles();
                }
            }

            $uniqueStudents = array_unique($processedStudents);
            $studentCount = count($uniqueStudents);

            if (!empty($uniqueStudents)) {
                $this->updateProgress(85, 'processing', "Generating remarks for {$studentCount} students...");
                $this->generateRemarksInBatches($uniqueStudents, 50);
            }

            $subjectTypeName = $this->subjectType === 'klass_subjects' ? 'class subjects' : 'optional subjects';

            $this->updateProgress(100, 'completed',
                "Successfully recalculated grades for {$subjectsProcessed} subjects, {$processedTests} tests, affecting {$studentCount} students."
            );

            Log::info("Grade recalculation completed successfully", [
                'job_id' => $this->jobId,
                'class_id' => $this->classId,
                'subjects_processed' => $subjectsProcessed,
                'tests_processed' => $processedTests,
                'students_affected' => $studentCount,
                'subject_type' => $subjectTypeName
            ]);

            $lock->release();

        } catch (Exception $e) {
            Log::error('Fatal error during grade recalculation job', [
                'job_id' => $this->jobId,
                'class_id' => $this->classId,
                'subject_type' => $this->subjectType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->updateProgress(0, 'failed', 'Recalculation failed: ' . $e->getMessage());
            $lock->release();
            $this->fail($e);
        }
    }

    private function getGradeSubjectIdsByType(int $gradeId, int $termId, string $subjectType): Collection{
        if ($subjectType === 'klass_subjects') {
            return KlassSubject::where('grade_id', $gradeId)
                ->where('term_id', $termId)
                ->where('active', true)
                ->pluck('grade_subject_id')
                ->unique();
        } else {
            return OptionalSubject::where('grade_id', $gradeId)
                ->where('term_id', $termId)
                ->where('active', true)
                ->pluck('grade_subject_id')
                ->unique();
        }
    }

    private function processSubjectGrades(int $gradeSubjectId, int $termId, int $year): array{
        $processedStudents = [];
        $processedTests = 0;
        $chunkSize = 200;

        $tests = Test::where('grade_subject_id', $gradeSubjectId)
            ->where('term_id', $termId)
            ->where('year', $year)
            ->get(['id', 'type', 'out_of']);

        if ($tests->isEmpty()) {
            return ['students' => [], 'tests' => 0];
        }

        $testIds = $tests->pluck('id')->all();
        $caTestIds = $tests->where('type', 'CA')->pluck('id')->all();

        DB::transaction(function () use ($testIds, $gradeSubjectId, $termId, $year, $chunkSize, &$processedStudents, &$processedTests) {
            StudentTest::whereIn('test_id', $testIds)
                ->whereNotNull('score')
                ->with('test')
                ->chunkById($chunkSize, function ($chunk) use ($gradeSubjectId, $termId, $year, &$processedStudents, &$processedTests) {
                    foreach ($chunk as $st) {
                        if ($st->test->out_of == 0) continue;

                        $processedTests++;
                        $processedStudents[] = $st->student_id;

                        $percentage = round($st->score / $st->test->out_of * 100);
                        $gradeObj = $this->getGradePerSubject($gradeSubjectId, $percentage);

                        $st->update([
                            'percentage' => $percentage,
                            'grade' => $gradeObj->grade,
                            'points' => $gradeObj->points,
                        ]);

                        if ($st->test->type === 'Exam' && $comment = AssessmentHelper::getRandomCommentForScore($percentage)) {
                            SubjectComment::updateOrCreate(
                                [
                                    'student_test_id' => $st->id,
                                    'grade_subject_id' => $gradeSubjectId,
                                    'student_id' => $st->student_id,
                                    'term_id' => $termId,
                                    'year' => $year,
                                ],
                                [
                                    'user_id' => null,
                                    'remarks' => $comment,
                                ]
                            );
                        }
                    }
                });
        });

        if (!empty($caTestIds)) {
            $this->processCAverages($caTestIds, $gradeSubjectId);
        }

        return [
            'students' => array_unique($processedStudents), 
            'tests' => $processedTests
        ];
    }

    private function processCAverages(array $caTestIds, int $gradeSubjectId): void{
        $cas = StudentTest::whereIn('test_id', $caTestIds)
            ->whereNotNull('percentage')
            ->select('student_id', DB::raw('ROUND(AVG(percentage)) as avg_perc'))
            ->groupBy('student_id')
            ->get();

        foreach ($cas as $row) {
            $gradeObj = $this->getGradePerSubject($gradeSubjectId, $row->avg_perc);

            StudentTest::where('student_id', $row->student_id)
                ->whereIn('test_id', $caTestIds)
                ->update([
                    'avg_score' => $row->avg_perc,
                    'avg_grade' => $gradeObj->grade,
                ]);
        }
    }

    private function getGradePerSubject(int $gradeSubjectId, int $percentage): object{
        $gradingScale = GradingScale::where('grade_subject_id', $gradeSubjectId)
            ->where('min_score', '<=', $percentage)
            ->where('max_score', '>=', $percentage)
            ->first();

        if ($gradingScale) {
            return (object) [
                'grade' => $gradingScale->grade,
                'points' => $gradingScale->points,
            ];
        }
        return (object) ['grade' => 'N/A', 'points' => 0];
    }

    private function generateRemarksInBatches(array $studentIds, int $chunkSize): void{
        $remarkService = app(RemarkGenerationService::class);
        foreach (array_chunk($studentIds, $chunkSize) as $chunk) {
            foreach ($chunk as $studentId) {
                try {
                    $remarkService->generateRemarksForStudent($studentId);
                } catch (Exception $e) {
                    Log::warning("Failed to generate remarks for student {$studentId}: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Update job progress in cache for frontend polling
     */
    private function updateProgress(int $percentage, string $status, string $message): void{
        $progressKey = "recalc_progress_{$this->classId}_{$this->subjectType}_{$this->selectedTermId}";

        $data = [
            'job_id' => $this->jobId,
            'percentage' => $percentage,
            'status' => $status,
            'message' => $message,
            'updated_at' => now()->toIso8601String(),
        ];

        Cache::put($progressKey, $data, 7200); // Cache for 2 hours

        // Log the cache write for debugging
        Log::debug("Progress updated in cache", [
            'key' => $progressKey,
            'percentage' => $percentage,
            'status' => $status,
            'cache_driver' => config('cache.default'),
            'verification' => Cache::has($progressKey) ? 'verified' : 'FAILED'
        ]);
    }

}
