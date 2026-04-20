<?php

namespace App\Http\Controllers;

use App\Helpers\TermHelper;
use App\Http\Controllers\Controller;
use App\Imports\ExternalResultsImport;
use App\Models\ExternalExam;
use App\Models\ExternalExamResult;
use App\Models\ExternalExamSubjectMapping;
use App\Models\ExternalExamSubjectResult;
use App\Models\FinalKlass;
use App\Models\PerformanceTarget;
use App\Models\SchoolSetup;
use App\Models\Subject;
use App\Models\Term;
use Auth;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class ExternalResultsImportController extends Controller{
    public function __construct(){
        $this->middleware(function ($request, $next) {
            $this->authorize('manage-assessment');
            return $next($request);
        });
    }

    public function showImportForm(){
        try {
            $school_data = SchoolSetup::query()->latest('id')->first();
            $terms = Term::orderBy('year', 'desc')->orderBy('id', 'desc')->get();
            $resolvedMode = SchoolSetup::normalizeType($school_data->type ?? null) ?? SchoolSetup::TYPE_JUNIOR;
            $examTypeOptions = $this->getExamTypeOptionsForMode($resolvedMode);
            $defaultExamType = array_key_first($examTypeOptions) ?? 'JCE';
            $mappingSchoolType = $this->getSubjectMappingSchoolTypeForMode($resolvedMode);
            $mappingExamType = $this->getDefaultExamTypeForSchoolType($mappingSchoolType);
            $subjectMappingCatalog = $this->getSubjectMappingCatalog($mappingSchoolType, $mappingExamType);
            $showSubjectMappingTab = $mappingSchoolType === SchoolSetup::TYPE_SENIOR;
            $mappingTableReady = Schema::hasTable('external_exam_subject_mappings');
            $availableSubjects = Subject::query()
                ->where('level', $mappingSchoolType)
                ->orderBy('name')
                ->get(['id', 'name', 'abbrev']);
            $templateDownloads = $this->getTemplateDownloadsForExamTypes(array_keys($examTypeOptions));
            $supportedFormats = $this->getSupportedFormatsForExamTypes(array_keys($examTypeOptions));
            $subjectColumnReferences = $this->getSubjectColumnReferencesForExamTypes(array_keys($examTypeOptions));

            $existingSubjectMappings = [];
            if ($mappingTableReady) {
                $existingSubjectMappings = ExternalExamSubjectMapping::query()
                    ->where('school_type', $mappingSchoolType)
                    ->where('exam_type', $mappingExamType)
                    ->where('is_active', true)
                    ->pluck('subject_id', 'source_key')
                    ->toArray();
            }

            $suggestedSubjectMappings = $this->buildSuggestedSubjectMappings($subjectMappingCatalog, $availableSubjects);

            return view('finals.import.external-exams-import', compact(
                'school_data',
                'terms',
                'subjectMappingCatalog',
                'availableSubjects',
                'existingSubjectMappings',
                'suggestedSubjectMappings',
                'mappingExamType',
                'mappingSchoolType',
                'mappingTableReady',
                'showSubjectMappingTab',
                'examTypeOptions',
                'defaultExamType',
                'templateDownloads',
                'supportedFormats',
                'subjectColumnReferences',
            ));
        } catch (Exception $e) {
            Log::error('Error loading external results import form', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);
            return redirect()->back()->with('error', 'Failed to load import form. Please try again.');
        }
    }

    /**
     * @return array<string, string>
     */
    private function getExamTypeOptionsForMode(string $mode): array
    {
        $examTypeLabels = [
            'JCE' => 'JCE - Junior Certificate Examination',
            'BGCSE' => 'BGCSE - Botswana General Certificate',
            'PSLE' => 'PSLE - Primary School Leaving Examination',
        ];

        $examTypes = match (SchoolSetup::normalizeType($mode)) {
            SchoolSetup::TYPE_PRIMARY => ['PSLE'],
            SchoolSetup::TYPE_JUNIOR,
            SchoolSetup::TYPE_PRE_F3 => ['JCE'],
            SchoolSetup::TYPE_SENIOR => ['BGCSE'],
            SchoolSetup::TYPE_JUNIOR_SENIOR,
            SchoolSetup::TYPE_K12 => ['JCE', 'BGCSE'],
            default => ['JCE'],
        };

        return collect($examTypes)
            ->mapWithKeys(fn (string $examType) => [$examType => $examTypeLabels[$examType] ?? $examType])
            ->all();
    }

    private function getSubjectMappingSchoolTypeForMode(string $mode): string
    {
        $availableExamTypes = array_keys($this->getExamTypeOptionsForMode($mode));

        if (in_array('BGCSE', $availableExamTypes, true)) {
            return SchoolSetup::TYPE_SENIOR;
        }

        if (in_array('JCE', $availableExamTypes, true)) {
            return SchoolSetup::TYPE_JUNIOR;
        }

        return SchoolSetup::TYPE_PRIMARY;
    }

    private function getDefaultExamTypeForSchoolType(string $schoolType): string
    {
        return match (SchoolSetup::normalizeType($schoolType)) {
            SchoolSetup::TYPE_PRIMARY => 'PSLE',
            SchoolSetup::TYPE_SENIOR => 'BGCSE',
            default => 'JCE',
        };
    }

    /**
     * @param  array<int, string>  $examTypes
     * @return array<int, array{path: string, label: string}>
     */
    private function getTemplateDownloadsForExamTypes(array $examTypes): array
    {
        $templates = [
            'JCE' => [
                'path' => asset('templates/jce-results-template.xlsx'),
                'label' => 'Download JCE Template.xlsx',
            ],
            'BGCSE' => [
                'path' => asset('templates/bgcse-results-template.xlsx'),
                'label' => 'Download BGCSE Template.xlsx',
            ],
            'PSLE' => [
                'path' => asset('templates/primary-results-template.xlsx'),
                'label' => 'Download Primary Template.xlsx',
            ],
        ];

        return array_values(array_filter(array_map(
            fn (string $examType) => $templates[$examType] ?? null,
            $examTypes
        )));
    }

    /**
     * @param  array<int, string>  $examTypes
     * @return array<int, string>
     */
    private function getSupportedFormatsForExamTypes(array $examTypes): array
    {
        $formats = [
            'JCE' => 'Official JCE Grade Listing PDFs (Junior)',
            'BGCSE' => 'Official BEC BGCSE Results Broadsheet (Senior)',
            'PSLE' => 'Official PSLE Results Sheets (Primary)',
        ];

        return array_values(array_filter(array_map(
            fn (string $examType) => $formats[$examType] ?? null,
            $examTypes
        )));
    }

    /**
     * @param  array<int, string>  $examTypes
     * @return array<int, string>
     */
    private function getSubjectColumnReferencesForExamTypes(array $examTypes): array
    {
        $references = [
            'JCE' => 'JCE subject columns: Setswana, English, Mathematics, Science, Social Studies, Agriculture, Design and Technology, Moral Education, Home Economics, Religious Education, Art, Music, Physical Education',
            'BGCSE' => 'BGCSE subject columns: Mathematics, English, Science, Setswana, Design and Technology, Home Economics, Agriculture, Moral Education, Music, Physical Education, Art, Business Studies, Accounting, French, Social Studies',
            'PSLE' => 'PSLE subject columns: English, Mathematics, Science, Social Studies, Setswana, Agriculture, Creative Arts, Physical Education',
        ];

        return array_values(array_filter(array_map(
            fn (string $examType) => $references[$examType] ?? null,
            $examTypes
        )));
    }

    public function storeSubjectMappings(Request $request){
        if (!Schema::hasTable('external_exam_subject_mappings')) {
            return redirect()->back()->with('error', 'Subject mappings table is missing. Please run migrations first.');
        }

        $validated = $request->validate([
            'school_type' => 'required|string|max:30',
            'exam_type' => 'required|string|in:JCE,BGCSE,PSLE',
            'mappings' => 'nullable|array',
            'mappings.*' => 'nullable|integer|exists:subjects,id',
        ]);
        $validated['school_type'] = ucfirst(strtolower((string) $validated['school_type']));
        $validated['exam_type'] = strtoupper((string) $validated['exam_type']);

        try {
            $catalog = $this->getSubjectMappingCatalog($validated['school_type'], $validated['exam_type']);
            $catalogByKey = collect($catalog)->keyBy('source_key');

            DB::transaction(function () use ($validated, $catalogByKey) {
                foreach ($catalogByKey as $sourceKey => $catalogItem) {
                    $subjectId = $validated['mappings'][$sourceKey] ?? null;

                    if (empty($subjectId)) {
                        ExternalExamSubjectMapping::query()
                            ->where('school_type', $validated['school_type'])
                            ->where('exam_type', $validated['exam_type'])
                            ->where('source_key', $sourceKey)
                            ->delete();
                        continue;
                    }

                    ExternalExamSubjectMapping::query()->updateOrCreate(
                        [
                            'school_type' => $validated['school_type'],
                            'exam_type' => $validated['exam_type'],
                            'source_key' => $sourceKey,
                        ],
                        [
                            'source_code' => $catalogItem['source_code'] ?? null,
                            'source_label' => $catalogItem['source_label'],
                            'subject_id' => $subjectId,
                            'is_active' => true,
                        ]
                    );
                }
            });

            return redirect()->back()->with('message', 'Subject mappings saved successfully.');
        } catch (Exception $e) {
            Log::error('Failed to save external subject mappings', [
                'error' => $e->getMessage(),
                'school_type' => $validated['school_type'] ?? null,
                'exam_type' => $validated['exam_type'] ?? null,
                'user_id' => auth()->id(),
            ]);

            return redirect()->back()->with('error', 'Failed to save subject mappings: ' . $e->getMessage());
        }
    }

    private function getSubjectMappingCatalog(string $schoolType, string $examType): array{
        if ($schoolType === 'Senior' && $examType === 'BGCSE') {
            return [
                ['source_key' => 'english', 'source_code' => '0561 / 1234', 'source_label' => 'English Language / BSSE English', 'default_subject_name' => 'English'],
                ['source_key' => 'setswana', 'source_code' => '0562 / 1235', 'source_label' => 'Setswana / BSSE Setswana', 'default_subject_name' => 'Setswana'],
                ['source_key' => 'mathematics', 'source_code' => '0563 / 1236', 'source_label' => 'Mathematics / BSSE General Mathematics', 'default_subject_name' => 'Mathematics'],
                ['source_key' => 'science_single_award', 'source_code' => '0568', 'source_label' => 'Science Single Award', 'default_subject_name' => 'Science'],
                ['source_key' => 'science_double_award', 'source_code' => '0569', 'source_label' => 'Science Double Award', 'default_subject_name' => 'Double Science'],
                ['source_key' => 'chemistry', 'source_code' => '0570', 'source_label' => 'Chemistry', 'default_subject_name' => 'Chemistry'],
                ['source_key' => 'physics', 'source_code' => '0571', 'source_label' => 'Physics', 'default_subject_name' => 'Physics'],
                ['source_key' => 'biology', 'source_code' => '0572', 'source_label' => 'Biology', 'default_subject_name' => 'Biology'],
                ['source_key' => 'human_and_social_biology', 'source_code' => '0573', 'source_label' => 'Human and Social Biology', 'default_subject_name' => 'Biology'],
                ['source_key' => 'history', 'source_code' => '0583', 'source_label' => 'History', 'default_subject_name' => 'History'],
                ['source_key' => 'geography', 'source_code' => '0584', 'source_label' => 'Geography', 'default_subject_name' => 'Geography'],
                ['source_key' => 'social_studies', 'source_code' => '0585', 'source_label' => 'Social Studies', 'default_subject_name' => 'Social Studies'],
                ['source_key' => 'development_studies', 'source_code' => '0586', 'source_label' => 'Development Studies', 'default_subject_name' => 'Development Studies'],
                ['source_key' => 'literature_in_english', 'source_code' => '0587', 'source_label' => 'Literature in English', 'default_subject_name' => 'English Literature'],
                ['source_key' => 'religious_education', 'source_code' => '0588', 'source_label' => 'Religious Education', 'default_subject_name' => 'Religious Education'],
                ['source_key' => 'design_and_technology', 'source_code' => '0595', 'source_label' => 'Design and Technology', 'default_subject_name' => 'Design & Technology'],
                ['source_key' => 'art', 'source_code' => '0596 / 1261', 'source_label' => 'Art and Design / BSSE Visual Arts', 'default_subject_name' => 'Art'],
                ['source_key' => 'computer_studies', 'source_code' => '0597', 'source_label' => 'Computer Studies', 'default_subject_name' => 'Computer Studies'],
                ['source_key' => 'commerce', 'source_code' => '0598', 'source_label' => 'Commerce', 'default_subject_name' => 'Commerce'],
                ['source_key' => 'agriculture', 'source_code' => '0599', 'source_label' => 'Agriculture', 'default_subject_name' => 'Agriculture'],
                ['source_key' => 'food_and_nutrition', 'source_code' => '0611 / 1262', 'source_label' => 'Food and Nutrition / BSSE Food Studies', 'default_subject_name' => 'Food & Nutrition'],
                ['source_key' => 'fashion_and_fabrics', 'source_code' => '0612', 'source_label' => 'Fashion and Fabrics', 'default_subject_name' => 'Fashion & Fabrics'],
                ['source_key' => 'home_management', 'source_code' => '0613', 'source_label' => 'Home Management', 'default_subject_name' => 'Home Management'],
                ['source_key' => 'accounting', 'source_code' => '0614', 'source_label' => 'Accounting', 'default_subject_name' => 'Accounting'],
                ['source_key' => 'business_studies', 'source_code' => '0615', 'source_label' => 'Business Studies', 'default_subject_name' => 'Business Studies'],
                ['source_key' => 'physical_education', 'source_code' => '0616 / 1259', 'source_label' => 'Physical Education / BSSE Physical Education', 'default_subject_name' => 'Physical Education'],
                ['source_key' => 'music', 'source_code' => '0617 / 1258', 'source_label' => 'Music / BSSE Music', 'default_subject_name' => 'Music'],
                ['source_key' => 'french', 'source_code' => '0618', 'source_label' => 'French', 'default_subject_name' => 'French'],
                ['source_key' => 'add_mathematics', 'source_code' => '1237', 'source_label' => 'BSSE Scientific Mathematics', 'default_subject_name' => 'Add Mathematics'],
                ['source_key' => 'hospitality_and_tourism_studies', 'source_code' => '1254', 'source_label' => 'Hospitality and Tourism Studies', 'default_subject_name' => 'Hospitality and Tourism Studies'],
                ['source_key' => 'animal_production', 'source_code' => '1255', 'source_label' => 'Animal Production', 'default_subject_name' => 'Animal Production'],
                ['source_key' => 'field_crop_production', 'source_code' => '1256', 'source_label' => 'Field Crop Production', 'default_subject_name' => 'Field Crop Production'],
                ['source_key' => 'horticulture', 'source_code' => '1257', 'source_label' => 'Horticulture', 'default_subject_name' => 'Horticulture'],
            ];
        }

        return [];
    }

    private function buildSuggestedSubjectMappings(array $catalog, Collection $subjects): array{
        $lookup = $subjects->keyBy(function ($subject) {
            return strtolower(trim($subject->name));
        });

        $suggested = [];
        foreach ($catalog as $item) {
            $defaultName = strtolower(trim((string) ($item['default_subject_name'] ?? '')));
            if ($defaultName !== '' && $lookup->has($defaultName)) {
                $suggested[$item['source_key']] = $lookup->get($defaultName)->id;
            }
        }

        return $suggested;
    }

    public function externalExamImport(Request $request){
        $request->validate([
            'exam_type' => 'required|string|in:JCE,BGCSE,PSLE,MOCK,OTHER',
            'exam_session' => 'required|string|max:255',
            'exam_year' => 'required|integer|min:2020|max:' . (date('Y') + 1),
            'graduation_year' => 'nullable|integer|min:2020|max:' . (date('Y') + 2),
            'graduation_term_id' => 'required|exists:terms,id',
            'file' => 'required|file|mimes:xlsx,xls|max:10240',
            'centre_code' => 'nullable|string|max:50',
            'centre_name' => 'nullable|string|max:255',
            'import_notes' => 'nullable|string|max:1000',
            'cleanup_before_import' => 'nullable|boolean'
        ]);
    
        try {
            if ($request->has('cleanup_before_import') && $request->cleanup_before_import) {
                $this->cleanupExternalExamData($request->exam_year, $request->graduation_year);
            }

            $examData = [
                'exam_type' => $request->exam_type,
                'exam_session' => $request->exam_session,
                'exam_year' => $request->exam_year,
                'graduation_year' => $request->graduation_year ?? ($request->exam_year + 1),
                'graduation_term_id' => $request->graduation_term_id,
                'centre_code' => $request->centre_code,
                'centre_name' => $request->centre_name,
                'import_notes' => $request->import_notes,
                'original_filename' => $request->file('file')->getClientOriginalName()
            ];
    
            Log::info('Starting streamlined external exam import', [
                'exam_data' => $examData,
                'user_id' => auth()->id(),
                'file_size' => $request->file('file')->getSize(),
                'file_name' => $examData['original_filename'],
                'cleanup_performed' => $request->has('cleanup_before_import')
            ]);
    
            $import = new ExternalResultsImport($examData);
            $import->import($request->file('file'));
    
            $stats = $import->getImportStats();
            $failures = $import->failures();
            $externalExam = $import->getExternalExam();
    
            Log::info('External exam import completed', [
                'stats' => $stats,
                'failures_count' => count($failures),
                'external_exam_id' => $externalExam->id,
                'total_rows_processed' => $stats['total_rows'],
                'successful_imports' => $stats['successful_imports'],
                'overall_grades_calculated' => $stats['overall_grades_calculated']
            ]);
    
            $message = $this->buildDetailedSuccessMessage($stats, $externalExam);
            
            if (count($failures) > 0) {
                $this->logFailures($failures->toArray());
                $message .= " <i class='fa fa-exclamation-triangle'></i> Note: " . count($failures) . " rows encountered issues during processing. Please check the logs for details.";
            }

            if ($stats['subjects_unmapped'] > 0) {
                $message .= " Warning: {$stats['subjects_unmapped']} subject grades could not be mapped to final grade subjects.";
            }
    
            return redirect()->back()->with('message', $message)->with('import_stats', $stats)->with('external_exam_id', $externalExam->id);
                
        } catch (Exception $e) {
            Log::error('External exam import failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'request_data' => $request->except(['file'])
            ]);
    
            return redirect()->back()->with('error', 'Import failed: ' . $e->getMessage())->withInput();
        }
    }

    private function cleanupExternalExamData(int $examYear, ?int $graduationYear = null): void {
        $graduationYear ??= $examYear + 1;
        Log::info('Starting cleanup of external exam data', [
            'exam_year' => $examYear,
            'graduation_year' => $graduationYear,
            'user_id' => auth()->id()
        ]);
    
        try {
            DB::beginTransaction();
            $externalExamsQuery = ExternalExam::where('exam_year', $examYear);
            if ($graduationYear) {
                $externalExamsQuery->where('graduation_year', $graduationYear);
            }

            $deletedExamsCount = $externalExamsQuery->count();

            if ($deletedExamsCount > 0) {
                // Use chunking to process large datasets without memory issues
                $externalExamsQuery->chunkById(100, function ($exams) {
                    foreach ($exams as $exam) {
                        ExternalExamSubjectResult::whereHas('externalExamResult', function($query) use ($exam) {
                            $query->where('external_exam_id', $exam->id);
                        })->delete();

                        ExternalExamResult::where('external_exam_id', $exam->id)->delete();
                        $exam->delete();
                    }
                });

                Log::info('Cleanup completed', [
                    'deleted_exams' => $deletedExamsCount,
                    'exam_year' => $examYear,
                    'graduation_year' => $graduationYear
                ]);
            } else {
                Log::info('No external exam data found to cleanup', [
                    'exam_year' => $examYear,
                    'graduation_year' => $graduationYear
                ]);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to cleanup external exam data', [
                'error' => $e->getMessage(),
                'exam_year' => $examYear,
                'graduation_year' => $graduationYear
            ]);
            throw new Exception('Cleanup failed: ' . $e->getMessage());
        }
    }

    private function buildDetailedSuccessMessage(array $stats, $externalExam): string {
        $message = "Import completed successfully! ";
        $message .= "Processed {$stats['total_rows']} rows, ";
        $message .= "{$stats['successful_imports']} students imported, ";
        $message .= "{$stats['students_matched']} students matched from records.";
        
        if ($stats['overall_grades_calculated'] > 0) {
            $message .= " Overall grades calculated for {$stats['overall_grades_calculated']} students.";
        }
        
        if ($stats['subjects_mapped'] > 0 || $stats['subjects_unmapped'] > 0) {
            $message .= " Subject mapping: {$stats['subjects_mapped']} mapped successfully";
            if ($stats['subjects_unmapped'] > 0) {
                $message .= ", {$stats['subjects_unmapped']} unmapped";
            }
            $message .= ".";
        }
        
        $message .= " External exam record #{$externalExam->id} created for {$externalExam->exam_type} {$externalExam->exam_year}.";
        
        return $message;
    }

    private function logFailures(array $failures): void {
        foreach ($failures as $failure) {
            Log::warning('Import row failure', [
                'row' => $failure->row(),
                'attribute' => $failure->attribute(),
                'errors' => $failure->errors(),
                'values' => $failure->values()
            ]);
        }
    }

    public function storePerformanceTargets(Request $request){
        try {
            $validatedData = $request->validate([
                'academic_year' => 'required|integer|min:2020|max:' . (date('Y') + 5),
                'exam_type' => 'required|string|in:JCE,BGCSE,PSLE',
                'high_achievement_target' => 'required|numeric|min:0|max:100',
                'pass_rate_target' => 'required|numeric|min:0|max:100',
                'non_failure_target' => 'required|numeric|min:0|max:100',
                'notes' => 'nullable|string|max:1000'
            ]);

            if ($validatedData['high_achievement_target'] > $validatedData['pass_rate_target']) {
                return response()->json([
                    'success' => false,
                    'message' => 'High achievement target cannot be greater than pass rate target.'
                ], 422);
            }

            if ($validatedData['pass_rate_target'] > $validatedData['non_failure_target']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pass rate target cannot be greater than non-failure target.'
                ], 422);
            }

            DB::beginTransaction();
            $performanceTarget = PerformanceTarget::setTargetsForYear(
                $validatedData['academic_year'],
                $validatedData['exam_type'],
                $validatedData['high_achievement_target'],
                $validatedData['pass_rate_target'],
                $validatedData['non_failure_target'],
                $validatedData['notes'],
                Auth::id()
            );

            DB::commit();
            Log::info('Performance targets saved successfully', [
                'user_id' => Auth::id(),
                'academic_year' => $validatedData['academic_year'],
                'exam_type' => $validatedData['exam_type'],
                'targets' => [
                    'high_achievement' => $validatedData['high_achievement_target'],
                    'pass_rate' => $validatedData['pass_rate_target'],
                    'non_failure' => $validatedData['non_failure_target']
                ]
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Performance targets saved successfully!',
                    'data' => [
                        'id' => $performanceTarget->id,
                        'academic_year' => $performanceTarget->academic_year,
                        'exam_type' => $performanceTarget->exam_type,
                        'high_achievement_target' => $performanceTarget->high_achievement_target,
                        'pass_rate_target' => $performanceTarget->pass_rate_target,
                        'non_failure_target' => $performanceTarget->non_failure_target,
                        'updated_at' => $performanceTarget->updated_at->format('M d, Y h:i A')
                    ]
                ]);
            }
            return redirect()->back()->with('message', 'Performance targets saved successfully!');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error saving performance targets', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'request_data' => $request->except(['_token'])
            ]);

            $errorMessage = 'Failed to save performance targets. Please try again.';
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 500);
            }

            return redirect()->back()->with('error', $errorMessage);
        }
    }

    public function getPerformanceTargets(Request $request){
        try {
            $request->validate([
                'academic_year' => 'required|integer',
                'exam_type' => 'required|string|in:JCE,BGCSE,PSLE'
            ]);

            $targets = PerformanceTarget::where('academic_year', $request->academic_year)->where('exam_type', $request->exam_type)->first();
            if ($targets) {
                return response()->json([
                    'success' => true,
                    'exists' => true,
                    'data' => [
                        'id' => $targets->id,
                        'academic_year' => $targets->academic_year,
                        'exam_type' => $targets->exam_type,
                        'high_achievement_target' => $targets->high_achievement_target,
                        'high_achievement_label' => $targets->high_achievement_label,
                        'pass_rate_target' => $targets->pass_rate_target,
                        'pass_rate_label' => $targets->pass_rate_label,
                        'non_failure_target' => $targets->non_failure_target,
                        'non_failure_label' => $targets->non_failure_label,
                        'notes' => $targets->notes,
                        'updated_at' => $targets->updated_at->format('M d, Y h:i A'),
                        'updated_by' => $targets->updater ? $targets->updater->name : 'Unknown'
                    ]
                ]);
            }

            return response()->json([
                'success' => true,
                'exists' => false,
                'message' => 'No targets found for the selected year and exam type.'
            ]);

        } catch (Exception $e) {
            Log::error('Error fetching performance targets', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch performance targets.'
            ], 500);
        }
    }
}
