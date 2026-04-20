<?php

namespace App\Imports;

use App\Helpers\TermHelper;
use App\Models\ExternalExam;
use App\Models\ExternalExamResult;
use App\Models\ExternalExamSubjectMapping;
use App\Models\ExternalExamSubjectResult;
use App\Models\FinalStudent;
use App\Models\FinalGradeSubject;
use App\Services\Finals\FinalsContextDefinition;
use App\Services\Finals\FinalsContextRegistry;
use App\Services\Finals\ImportProfiles\FinalsImportProfile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\BeforeSheet;
use Carbon\Carbon;

class ExternalResultsImport implements ToModel, WithHeadingRow, WithStartRow, WithEvents, SkipsOnError, SkipsOnFailure{
    use Importable, SkipsErrors, SkipsFailures;

    protected $examData;
    protected $externalExam;
    protected $subjectMappings;
    protected $passGrades;
    protected $importStats;
    protected $requiredColumns;
    protected $optionalColumns;
    protected $subjectColumns;
    protected $headersProcessed = false;
    protected $rowsCount = 0;
    protected $finalGradeSubjects;
    protected $customSubjectIdMappings = [];
    protected $customSubjectNameMappings = [];
    protected FinalsImportProfile $profile;
    protected FinalsContextDefinition $definition;

    public function __construct(array $examData, ?FinalsImportProfile $profile = null){
        $this->examData = $examData;
        $this->profile = $profile ?? app(FinalsContextRegistry::class)->profile((string) ($examData['finals_context'] ?? 'junior'));
        $this->definition = $this->profile->definition();
        $this->subjectMappings = $this->getSubjectMappings();
        $this->passGrades = $this->definition->passGradeSet;

        $this->requiredColumns = $this->definition->requiredColumns;
        $this->optionalColumns = $this->definition->optionalColumns;
        
        $this->subjectColumns = [];

        $this->importStats = [
            'total_rows' => 0,
            'successful_imports' => 0,
            'failed_imports' => 0,
            'skipped_rows' => 0,
            'students_matched' => 0,
            'students_not_found' => 0,
            'subjects_mapped' => 0,
            'subjects_partially_mapped' => 0,
            'subjects_unmapped' => 0,
            'grading_scales_used' => 0,
            'default_points_used' => 0,
            'overall_grades_calculated' => 0,
            'calculation_errors' => 0,
            'errors' => [],
            'mapping_details' => [
                'mandatory_class' => 0,
                'optional_enrolled' => 0,
                'not_enrolled' => 0,
                'rollover_mapping_failed' => 0,
                'subject_not_found' => 0,
                'unmapped' => 0
            ]
        ];

        $this->createExternalExam();
        $this->loadFinalGradeSubjects();
        $this->loadCustomSubjectMappings();
    }

    public function startRow(): int{
        return 2;
    }

    public function model(array $row){
        if ($this->isEmptyRow($row)) {
            Log::debug('Skipping empty row', ['row_number' => $this->rowsCount + 2]);
            return null;
        }
        
        $this->rowsCount++;
        $this->importStats['total_rows']++;
        
        if (!$this->headersProcessed) {
            $this->processHeaders($row);
            $this->headersProcessed = true;
        }
        
        try {
            Log::info('Processing student row', [
                'row_number' => $this->rowsCount,
                'exam_number' => $row['exam_number'] ?? 'Unknown',
                'available_keys' => array_keys($row)
            ]);
            
            $this->validateRowData($row);
            $student = $this->findStudent($row['exam_number']);
            
            if (!$student) {
                $this->importStats['students_not_found']++;
                Log::warning("Student not found", [
                    'exam_number' => $row['exam_number'],
                    'row_number' => $this->rowsCount
                ]);
                return null;
            }
            
            $this->importStats['students_matched']++;
            $examResult = $this->createExternalExamResult($student, $row);
            $this->processSubjectGrades($examResult, $row);
            
            $calculatedResult = $this->calculateOverallGrade($examResult);
            if ($calculatedResult) {
                $updateData = [
                    'overall_grade' => $calculatedResult['grade'],
                    'overall_points' => $calculatedResult['points']
                ];
                
                $examResult->update($updateData);
                $this->importStats['overall_grades_calculated']++;
                
                Log::info('Overall grade calculated and updated', [
                    'exam_number' => $examResult->exam_number,
                    'calculated_grade' => $calculatedResult['grade'],
                    'calculated_points' => $calculatedResult['points'],
                    'points_breakdown' => $calculatedResult['breakdown']
                ]);
            } else {
                Log::warning('Could not calculate overall grade', [
                    'exam_number' => $examResult->exam_number,
                    'student_id' => $student->id
                ]);
                $this->importStats['calculation_errors']++;
            }
            
            $examResult->recalculateStats();
            $this->importStats['successful_imports']++;
            
            Log::info('Student row processed successfully', [
                'exam_number' => $row['exam_number'],
                'student_id' => $student->id,
                'exam_result_id' => $examResult->id,
                'final_grade' => $calculatedResult['grade'] ?? null,
                'final_points' => $calculatedResult['points'] ?? null
            ]);
            
            return $examResult;
            
        } catch (\Exception $e) {
            $this->importStats['failed_imports']++;
            $this->importStats['errors'][] = [
                'row' => $this->rowsCount,
                'exam_number' => $row['exam_number'] ?? 'Unknown',
                'error' => $e->getMessage()
            ];
            
            Log::error('Failed to import student row', [
                'row_number' => $this->rowsCount,
                'exam_number' => $row['exam_number'] ?? 'Unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return null;
        }
    }

    protected function isEmptyRow(array $row){
        if (empty($row['exam_number'] ?? null)) {
            return true;
        }

        // If subject columns haven't been identified yet (first row), allow row through
        // so that processHeaders() can run and identify the subject columns
        if (empty($this->subjectColumns)) {
            return false;
        }

        foreach ($this->subjectColumns as $subjectColumn) {
            if (!empty($row[$subjectColumn] ?? null) && $row[$subjectColumn] !== '-') {
                return false;
            }
        }

        return true;
    }

    protected function processHeaders(array $row){
        $headers = array_keys($row);
        Log::info('Processing Excel headers', [
            'headers' => $headers,
            'total_headers' => count($headers),
            'required_columns' => $this->requiredColumns
        ]);

        $missingColumns = [];
        foreach ($this->requiredColumns as $required) {
            if (!array_key_exists($required, $row)) {
                $missingColumns[] = $required;
            }
        }

        if (!empty($missingColumns)) {
            throw new \Exception('Missing required columns: ' . implode(', ', $missingColumns));
        }

        $this->subjectColumns = [];
        foreach ($headers as $header) {
            if (in_array($header, $this->requiredColumns) || in_array($header, $this->optionalColumns)) {
                continue;
            }
            
            if (isset($this->subjectMappings[$header])) {
                $this->subjectColumns[] = $header;
                Log::info("Found subject column", [
                    'header' => $header,
                    'mapped_to' => $this->subjectMappings[$header]
                ]);
            }
        }

        Log::info('Subject columns identified', [
            'subject_columns' => $this->subjectColumns,
            'count' => count($this->subjectColumns)
        ]);

        if (empty($this->subjectColumns)) {
            throw new \Exception('No valid subject columns found. Please check column headers match expected subject names.');
        }

        $this->updateExternalExamColumns($headers);
    }

    protected function updateExternalExamColumns(array $headers){
        try {
            $this->externalExam->update([
                'excel_columns' => $headers
            ]);
            
            Log::info('Excel columns updated', [
                'external_exam_id' => $this->externalExam->id,
                'columns' => $headers
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update excel columns', [
                'error' => $e->getMessage(),
                'external_exam_id' => $this->externalExam->id
            ]);
        }
    }

    protected function validateRowData(array $row){
        $errors = [];

        if (empty($row['exam_number'] ?? null)) {
            $errors[] = 'exam_number is required';
        }

        $warnings = [];
        if (empty($row['first_name'] ?? null)) {
            $warnings[] = 'first_name is missing';
        }
        if (empty($row['last_name'] ?? null)) {
            $warnings[] = 'last_name is missing';
        }

        if (!empty($warnings)) {
            Log::warning('Optional fields missing', [
                'warnings' => $warnings,
                'exam_number' => $row['exam_number'] ?? 'Unknown'
            ]);
        }

        if (!empty($errors)) {
            throw new \Exception('Validation failed: ' . implode(', ', $errors));
        }

        $hasValidSubjectGrade = false;
        foreach ($this->subjectColumns as $subjectColumn) {
            $grade = $row[$subjectColumn] ?? null;
            if (!empty($grade) && $grade !== '-' && $this->isValidGrade(strtoupper(trim($grade)))) {
                $hasValidSubjectGrade = true;
                break;
            }
        }

        if (!$hasValidSubjectGrade) {
            throw new \Exception('No valid subject grades found for student');
        }
    }

    protected function findStudent($examNumber){
        try {
            // Normalize exam number: convert to string and zero-pad to 4 digits
            $normalizedExamNumber = str_pad((int) $examNumber, 4, '0', STR_PAD_LEFT);
            $graduationYear = $this->examData['graduation_year'] ?? ($this->examData['exam_year'] + 1);

            Log::debug('Finding student', [
                'original_exam_number' => $examNumber,
                'normalized_exam_number' => $normalizedExamNumber,
                'graduation_year' => $graduationYear
            ]);

            return FinalStudent::where('exam_number', $normalizedExamNumber)
                              ->where('graduation_year', $graduationYear)
                              ->first();
        } catch (\Exception $e) {
            Log::warning('Error finding student', [
                'exam_number' => $examNumber,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    protected function createExternalExamResult(FinalStudent $student, array $row){
        return ExternalExamResult::create([
            'external_exam_id' => $this->externalExam->id,
            'final_student_id' => $student->id,
            'exam_number' => $row['exam_number'],
            'excel_class_name' => null,
            'overall_grade' => null,
            'overall_points' => 0.0,
            'total_subjects' => 0,
            'passes' => 0,
            'pass_percentage' => 0
        ]);
    }

    protected function processSubjectGrades(ExternalExamResult $examResult, array $row){
        foreach ($this->subjectColumns as $subjectColumn) {
            $grade = $row[$subjectColumn] ?? null;
            
            if (empty($grade) || $grade === '-' || $grade === '' || $grade === null) {
                continue;
            }

            $grade = strtoupper(trim($grade));
            if (!$this->isValidGrade($grade)) {
                Log::warning('Invalid subject grade', [
                    'subject' => $subjectColumn,
                    'grade' => $grade,
                    'exam_number' => $examResult->exam_number
                ]);
                continue;
            }

            $subjectCode = $this->subjectMappings[$subjectColumn] ?? null;
            $finalGradeSubject = $this->findFinalGradeSubject($subjectColumn, $examResult->finalStudent);
            
            $gradePoints = $this->getGradePoints($grade, $finalGradeSubject);
            ExternalExamSubjectResult::create([
                'external_exam_result_id' => $examResult->id,
                'final_grade_subject_id' => $finalGradeSubject ? $finalGradeSubject->id : null,
                'subject_code' => $subjectCode,
                'subject_name' => $this->formatSubjectName($subjectColumn),
                'grade' => $grade,
                'grade_points' => $gradePoints,
                'is_pass' => in_array($grade, $this->passGrades),
                'is_mapped' => $finalGradeSubject !== null,
                'was_taken' => true, 
                'mapping_notes' => $finalGradeSubject ? 'Auto-mapped to final grade subject' : 'No matching final grade subject found'
            ]);

            if ($finalGradeSubject) {
                $this->importStats['subjects_mapped']++;
            } else {
                $this->importStats['subjects_unmapped']++;
            }

            Log::info('Subject grade processed', [
                'subject' => $subjectColumn,
                'grade' => $grade,
                'grade_points' => $gradePoints,
                'subject_code' => $subjectCode,
                'mapped' => $finalGradeSubject !== null,
                'exam_number' => $examResult->exam_number
            ]);
        }
    }

    protected function calculateOverallGrade(ExternalExamResult $examResult){
        $student = $examResult->finalStudent;
        $isForeigner = $student->nationality !== 'Motswana';
        
        $subjectResults = $examResult->subjectResults()->with('finalGradeSubject.subject')->get();
        if ($subjectResults->isEmpty()) {
            return null;
        }
        
        $mandatoryPoints = 0;
        $optionalPoints = [];
        $corePoints = [];
        
        foreach ($subjectResults as $subjectResult) {
            $points = $subjectResult->grade_points;
            $finalGradeSubject = $subjectResult->finalGradeSubject;
            
            if (!$finalGradeSubject) {
                continue;
            }
            
            $subjectName = $finalGradeSubject->subject->name;
            
            if ($subjectName === "Setswana") {
                // Setswana should only count for Motswana students.
                if (!$isForeigner) {
                    $mandatoryPoints += $points;
                } elseif (!$finalGradeSubject->type) {
                    $optionalPoints[] = $points;
                } else {
                    $corePoints[] = $points;
                }
                continue;
            }
            
            if ($finalGradeSubject->mandatory) {
                $mandatoryPoints += $points;
            } elseif (!$finalGradeSubject->mandatory && !$finalGradeSubject->type) {
                $optionalPoints[] = $points;
            } elseif (!$finalGradeSubject->mandatory && $finalGradeSubject->type) {
                $corePoints[] = $points;
            }
        }
        
        rsort($optionalPoints);
        rsort($corePoints);
        
        if ($isForeigner) {
            $bestOptionalPoints = array_sum(array_slice($optionalPoints, 0, 2));
            $remainingOptionals = array_slice($optionalPoints, 2);
        } else {
            $bestOptionalPoints = count($optionalPoints) ? $optionalPoints[0] : 0;
            $remainingOptionals = array_slice($optionalPoints, 1);
        }
        
        $combinedRemaining = array_merge($remainingOptionals, $corePoints);
        rsort($combinedRemaining);
        $bestFromCombined = array_sum(array_slice($combinedRemaining, 0, 2));
        
        $rawTotalPoints = $mandatoryPoints + $bestOptionalPoints + $bestFromCombined;
        $totalPoints = $this->normalizeOverallPoints($rawTotalPoints, $examResult->exam_number);
        $overallGrade = $this->determineGradeFromPoints($totalPoints, $student->graduation_grade_id);
        
        Log::info('Overall grade calculated', [
            'exam_number' => $examResult->exam_number,
            'nationality' => $student->nationality,
            'is_foreigner' => $isForeigner,
            'mandatory_points' => $mandatoryPoints,
            'best_optional_points' => $bestOptionalPoints,
            'best_from_combined' => $bestFromCombined,
            'raw_total_points' => $rawTotalPoints,
            'total_points' => $totalPoints,
            'was_capped' => $rawTotalPoints !== $totalPoints,
            'calculated_grade' => $overallGrade
        ]);
        
        return [
            'grade' => $overallGrade,
            'points' => $totalPoints,
            'breakdown' => [
                'mandatory_points' => $mandatoryPoints,
                'best_optional_points' => $bestOptionalPoints,
                'best_from_combined' => $bestFromCombined,
                'raw_total_points' => $rawTotalPoints,
                'was_capped' => $rawTotalPoints !== $totalPoints
            ]
        ];
    }

    protected function normalizeOverallPoints(float $points, ?string $examNumber = null): float{
        $normalized = round($points, 1);

        if ($normalized < 0) {
            Log::warning('Overall points below expected minimum. Clamping to 0.', [
                'exam_number' => $examNumber,
                'raw_points' => $points,
            ]);
            return 0.0;
        }

        if ($normalized > 63) {
            Log::warning('Overall points exceeded maximum. Clamping to 63.', [
                'exam_number' => $examNumber,
                'raw_points' => $points,
            ]);
            return 63.0;
        }

        return $normalized;
    }

    protected function determineGradeFromPoints($totalPoints, $gradeId){
        try {
            $academicYear = $this->getAcademicYearFromGrade($gradeId);
            
            if (!$academicYear) {
                return null;
            }
            
            $pointsMatrix = DB::table('overall_points_matrix')
                ->where('academic_year', $academicYear)
                ->where('min', '<=', $totalPoints)
                ->where('max', '>=', $totalPoints)
                ->first();
            
            return $pointsMatrix ? $pointsMatrix->grade : null;
            
        } catch (\Exception $e) {
            Log::warning('Error determining grade from points', [
                'total_points' => $totalPoints,
                'grade_id' => $gradeId,
                'error' => $e->getMessage()
            ]);
            
            return $this->getSimpleGradeFromPoints($totalPoints);
        }
    }

    protected function getAcademicYearFromGrade($gradeId){
        try {
            $grade = DB::table('grades')->where('id', $gradeId)->first();
            if (!$grade) return null;
            
            $gradeName = $grade->name;
            if (preg_match('/F(\d)/', $gradeName, $matches)) {
                return 'F' . $matches[1];
            }
            
            return $gradeName;
        } catch (\Exception $e) {
            Log::warning('Error getting academic year from grade', [
                'grade_id' => $gradeId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    protected function getSimpleGradeFromPoints($totalPoints){
        if ($totalPoints >= 63) return 'Merit';
        if ($totalPoints >= 55) return 'A';
        if ($totalPoints >= 41) return 'B';
        if ($totalPoints >= 28) return 'C';
        if ($totalPoints >= 14) return 'D';
        if ($totalPoints >= 8) return 'E';
        return 'U';
    }

    protected function getExcelToSubjectNameMapping(){
        $jceMappings = [
            'mathematics' => 'Mathematics',
            'english' => 'English', 
            'science' => 'Science',
            'social_studies' => 'Social Studies',
            'setswana' => 'Setswana',
            'agriculture' => 'Agriculture',
            'moral_education' => 'Moral Education',
            'art' => 'Art',
            'design_and_technology' => 'Design & Technology',
            'music' => 'Music',
            'religious_education' => 'Religious Education',
            'physical_education' => 'Physical Education',
            'home_economics' => 'Home Economics',
            'office_procedures' => 'Office Procedures',
            'accounting' => 'Accounting'
        ];

        return array_merge($jceMappings, $this->profile->defaultSubjectNameMap(), $this->customSubjectNameMappings);
    }

    protected function findFinalGradeSubject($subjectColumn, $student){
        if (isset($this->customSubjectIdMappings[$subjectColumn])) {
            $mappedSubjectId = $this->customSubjectIdMappings[$subjectColumn];
            $mappedById = $this->finalGradeSubjects
                ->where('grade_id', $student->graduation_grade_id)
                ->where('graduation_year', $student->graduation_year)
                ->where('subject_id', $mappedSubjectId)
                ->first();

            if ($mappedById) {
                return $mappedById;
            }
        }

        $subjectNameMapping = $this->getExcelToSubjectNameMapping();
        $exactSubjectName = $subjectNameMapping[$subjectColumn] ?? $this->formatSubjectName($subjectColumn);
        
        $result = $this->finalGradeSubjects
            ->where('grade_id', $student->graduation_grade_id)
            ->where('graduation_year', $student->graduation_year)
            ->filter(function($fgs) use ($exactSubjectName) {
                return strtolower($fgs->subject->name) === strtolower($exactSubjectName);
            })->first();
        
        return $result;
    }

    protected function formatSubjectName($subjectColumn){
        return ucwords(str_replace('_', ' ', $subjectColumn));
    }

    protected function loadFinalGradeSubjects(){
        $this->finalGradeSubjects = FinalGradeSubject::with('subject')
            ->whereHas('grade', function ($query) {
                $query->whereIn('grades.name', $this->definition->graduationGradeNames);
            })
            ->where('graduation_year', $this->examData['graduation_year'] ?? ($this->examData['exam_year'] + 1))
            ->get();
    }

    protected function loadCustomSubjectMappings(){
        try {
            $schoolType = $this->definition->schoolType;
            $examType = $this->definition->examType;

            if (empty($examType) || empty($schoolType)) {
                return;
            }

            $rows = ExternalExamSubjectMapping::query()
                ->with('subject:id,name')
                ->where('exam_type', $examType)
                ->where('school_type', $schoolType)
                ->where('is_active', true)
                ->get(['source_key', 'subject_id']);

            foreach ($rows as $row) {
                if (empty($row->source_key) || empty($row->subject_id)) {
                    continue;
                }

                $this->customSubjectIdMappings[$row->source_key] = (int) $row->subject_id;
                if ($row->subject && !empty($row->subject->name)) {
                    $this->customSubjectNameMappings[$row->source_key] = $row->subject->name;
                }
            }

            Log::info('Loaded custom external subject mappings', [
                'exam_type' => $examType,
                'school_type' => $schoolType,
                'count' => count($this->customSubjectIdMappings),
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to load custom subject mappings', [
                'exam_type' => $this->examData['exam_type'] ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function getGradePoints(string $grade, $finalGradeSubject = null){
        if ($finalGradeSubject) {
            try {
                $gradeSubjectId = $finalGradeSubject->original_grade_subject_id;
                $examYear = $this->examData['exam_year'];
                
                $gradingScale = DB::table('grading_scales')
                    ->where('grade_subject_id', $gradeSubjectId)
                    ->where('grade', $grade)
                    ->where('year', $examYear)
                    ->first();
                
                if ($gradingScale) {
                    Log::info('Found grading scale', [
                        'grade' => $grade,
                        'grade_subject_id' => $gradeSubjectId,
                        'year' => $examYear,
                        'points' => $gradingScale->points
                    ]);
                    
                    return (float) $gradingScale->points;
                }
                
                $gradingScale = DB::table('grading_scales')
                    ->where('grade_subject_id', $gradeSubjectId)
                    ->where('grade', $grade)
                    ->orderBy('year', 'desc')
                    ->first();
                    
                if ($gradingScale) {
                    Log::info('Found grading scale (fallback)', [
                        'grade' => $grade,
                        'grade_subject_id' => $gradeSubjectId,
                        'fallback_year' => $gradingScale->year,
                        'points' => $gradingScale->points
                    ]);
                    
                    return (float) $gradingScale->points;
                }
                
            } catch (\Exception $e) {
                Log::warning('Error fetching grading scale', [
                    'grade' => $grade,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        $defaultPoints = [
            'A' => 9.0,
            'B' => 7.0,
            'C' => 5.0,
            'D' => 3.0,
            'E' => 1.0,
            'U' => 0.0
        ];
        
        $points = $defaultPoints[$grade] ?? 0.0;
        
        Log::info('Using default grade points', [
            'grade' => $grade,
            'points' => $points,
            'reason' => $finalGradeSubject ? 'No grading scale found' : 'No final grade subject'
        ]);
        
        return $points;
    }

    protected function getSubjectMappings(){
        return $this->profile->subjectCodeMap();
    }

    protected function createExternalExam(){
        $this->externalExam = ExternalExam::create([
            'exam_type' => $this->examData['exam_type'],
            'exam_session' => $this->examData['exam_session'],
            'exam_year' => $this->examData['exam_year'],
            'centre_code' => $this->examData['centre_code'] ?? null,
            'centre_name' => $this->examData['centre_name'] ?? null,
            'graduation_year' => $this->examData['graduation_year'] ?? ($this->examData['exam_year'] + 1),
            'graduation_term_id' => $this->examData['graduation_term_id'] ?? TermHelper::getCurrentTerm()->id,
            'import_date' => Carbon::now()->toDateString(),
            'imported_by' => auth()->id(),
            'import_notes' => $this->examData['import_notes'] ?? null,
            'original_filename' => $this->examData['original_filename'] ?? null
        ]);

        Log::info('External exam record created', [
            'external_exam_id' => $this->externalExam->id,
            'exam_type' => $this->examData['exam_type'],
            'exam_year' => $this->examData['exam_year']
        ]);
    }

    protected function isValidGrade(string $grade){
        $validGrades = ['A', 'B', 'C', 'D', 'E', 'U'];
        return in_array($grade, $validGrades);
    }

    public function getImportStats(){
        return $this->importStats;
    }

    public function getExternalExam(){
        return $this->externalExam;
    }

    public function registerEvents(): array{
        return [
            BeforeImport::class => function () {
                DB::beginTransaction();
                Log::info('Starting external results import transaction');
            },
            BeforeSheet::class => function ($event) {
                $sheetName = $event->getSheet()->getTitle();
                Log::info('Processing Excel sheet', [
                    'sheet_name' => $sheetName
                ]);
            },
            AfterImport::class => function () {
                if ($this->importStats['successful_imports'] > 0 && count($this->failures()) == 0) {
                    DB::commit();
                    Log::info('External results import committed successfully', [
                        'rows_processed' => $this->rowsCount,
                        'stats' => $this->importStats
                    ]);
                } else {
                    DB::rollBack();
                    Log::warning('External results import rolled back', [
                        'rows_processed' => $this->rowsCount,
                        'failures' => count($this->failures()),
                        'stats' => $this->importStats
                    ]);
                }
            },
        ];
    }
}
