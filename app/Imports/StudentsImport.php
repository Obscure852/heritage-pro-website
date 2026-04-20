<?php

namespace App\Imports;

use App\Models\Sponsor;
use App\Models\Student;
use App\Models\Term;
use App\Models\Klass;
use App\Models\Grade;
use App\Models\JCE;
use App\Models\PSLE;
use App\Models\StudentTerm;
use App\Models\User;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\BeforeImport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\SchoolSetup;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class StudentsImport implements ToModel, WithHeadingRow, WithEvents, WithValidation, SkipsOnFailure, WithChunkReading{
    use Importable, SkipsFailures;

    public $rowsCount = 0;
    private $term;
    private $schoolType;
    private ?string $resolvedSchoolType = null;
    private $sponsors;
    private $grades;
    private $klasses;

    public function __construct(private $termId){
        $this->term = Term::find($this->termId);

        if (!$this->term) {
            Log::error("Term not found for term ID: {$this->termId}. Import cannot proceed.");
            return;
        }

        // Pre-cache reference data to avoid ~2,400 redundant queries
        $this->schoolType = SchoolSetup::first();
        $this->resolvedSchoolType = SchoolSetup::normalizeType($this->schoolType?->type);
        $this->sponsors = Sponsor::pluck('id', 'connect_id');
        $this->grades = Grade::where('term_id', $this->termId)->get()->keyBy('name');
        $this->klasses = Klass::where('term_id', $this->termId)
            ->get()
            ->keyBy(function ($klass) {
                return $this->klassCacheKey($klass->name, $klass->grade_id);
            });
    }

    public function chunkSize(): int{
        return 100;
    }

    public function model(array $row){
        if ($this->isEmptyRow($row)) {
            return null;
        }

        if (!$this->term) {
            Log::warning("Term not set. Skipping row {$this->rowsCount}.");
            return null;
        }

        try {
            $this->rowsCount++;
            $sponsorId = $this->getSponsorId($row);
            $grade = $this->getGrade($row);

            $klass = $this->getOrCreateKlass($row, $grade);
            if (!$klass) {
                $failure = new Failure(
                    $this->rowsCount,
                    'class',
                    ["Class not found or cannot be created"],
                    $row
                );
                $this->onFailure($failure);
                return null;
            }

            if (!$this->schoolType) {
                Log::error("No SchoolSetup record found. Cannot determine school type. Row {$this->rowsCount} skipped.");
                $failure = new Failure(
                    $this->rowsCount,
                    'schoolType',
                    ["School type not found"],
                    $row
                );
                $this->onFailure($failure);
                return null;
            }

            $student = $this->createStudent($row, $sponsorId);
            $this->linkStudentToKlass($klass, $student, $grade);
            $this->linkStudentToTerm($student, $grade);

            $examImportType = $this->resolveExternalExamImportType($grade);

            if ($examImportType === 'psle') {
                $this->savePsleGrade($student, $row);
            } elseif ($examImportType === 'jce') {
                $this->saveJceGrade($student, $row);
            }

            return $student;

        } catch (\Exception $e) {
            Log::error("Error importing student: " . $e->getMessage());
            $failure = new Failure(
                $this->rowsCount,
                'Error',
                [$e->getMessage()],
                $row
            );
            $this->onFailure($failure);
            return null;
        }
    }

    private function closePreviousTerms(){
        if (!$this->term) {
            Log::warning("Term not set. Cannot close previous terms.");
            return;
        }

        try {
            $closedCount = Term::where('year', $this->term->year)
                ->where('term', '<', $this->term->term)
                ->where('closed', 0)
                ->update(['closed' => 1]);

            if ($closedCount > 0) {
                Log::info("Closed {$closedCount} previous terms for year {$this->term->year}, current term {$this->term->term}");
            } else {
                Log::info("No previous terms to close for year {$this->term->year}, term {$this->term->term}");
            }
        } catch (\Exception $e) {
            Log::error("Error closing previous terms: " . $e->getMessage());
            throw $e;
        }
    }

    private function isEmptyRow(array $row){
        return collect($row)->every(fn($value) => empty($value) || is_null($value));
    }

    private function getSponsorId(array $row){
        $connectId = UserImport::sanitizeData($row['connect_id'] ?? null);
        return $this->sponsors[$connectId] ?? null;
    }

    private function getGrade(array $row){
        $gradeName = UserImport::sanitizeData($row['grade'] ?? null);
        return $this->grades[$gradeName] ?? null;
    }

    private function createStudent(array $row, ?int $sponsorId){
        $normalizedStatus = $this->normalizeStudentStatus($row['status'] ?? null);

        return Student::create([
            'connect_id'    => $sponsorId,
            'sponsor_id'    => $sponsorId,
            'first_name'    => $this->formatName(strtolower(trim($row['first_name'] ?? ''))),
            'last_name'     => $this->formatName(strtolower(trim($row['last_name'] ?? ''))),
            'middle_name'   => $this->formatName(strtolower(trim($row['middle_name'] ?? ''))),
            'gender'        => UserImport::sanitizeData($row['gender'] ?? null),
            'date_of_birth' => $this->forceFormatDateOfBirth(trim($row['date_of_birth'] ?? '')),
            'nationality'   => $this->formatName($row['nationality'] ?? null),
            'id_number'     => UserImport::sanitizeData($row['id_number'] ?? null),
            'status'        => $normalizedStatus,
            'type'          => ucfirst(strtolower(trim(UserImport::sanitizeData($row['type'] ?? null)))),
            'is_boarding'   => strtolower(trim($row['boarding'] ?? '')) === 'boarding',
            'year'          => $this->term->year,
            'password'      => Hash::make(Str::random(10)),
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);
    }

    private function linkStudentToKlass(Klass $klass, Student $student, Grade $grade){
        try {
            DB::table('klass_student')->insert([
                'klass_id' => $klass->id,
                'student_id' => $student->id,
                'active' => true,
                'term_id' => $this->termId,
                'grade_id' => $grade->id,
                'year' => $this->term->year,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error("Error linking student {$student->id} to klass: " . $e->getMessage());
        }
    }

    private function linkStudentToTerm(Student $student, Grade $grade){
        try {
            StudentTerm::create([
                'student_id' => $student->id,
                'term_id' => $this->termId,
                'grade_id' => $grade->id,
                'year' => $this->term->year,
                'status' => $student->status,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error("Error linking student {$student->id} to term: " . $e->getMessage());
        }
    }

    private function getOrCreateKlass(array $row, Grade $grade){
        $klassName = UserImport::sanitizeData($row['class'] ?? null);
        $klassCacheKey = $this->klassCacheKey($klassName, $grade->id);
        $klass = $this->klasses[$klassCacheKey] ?? null;

        if (!$klass) {
            try {
                $teacher = User::where('area_of_work', 'Teaching')->where('status', 'Current')->inRandomOrder()->first();

                if (!$teacher) {
                    Log::error("No teacher found to create a new class {$klassName}");
                    return null;
                }

                $klass = Klass::create([
                    'name' => $klassName,
                    'user_id' => $teacher->id,
                    'term_id' => $this->termId,
                    'grade_id' => $grade->id,
                    'active' => true,
                    'year' => $this->term->year,
                ]);

                // Add to cache so subsequent rows don't re-create it
                $this->klasses[$klassCacheKey] = $klass;
            } catch (\Exception $e) {
                Log::error("Error creating a new class {$klassName}: " . $e->getMessage());
            }
        }
        return $klass;
    }

    private function savePsleGrade(Student $student, array $row){
        $allowedGrades = ['A', 'B', 'C', 'D', 'E'];
        $overallGrade = UserImport::sanitizeData($row['overall_grade'] ?? null);

        if (empty($overallGrade)) {
            $overallGrade = $allowedGrades[array_rand($allowedGrades)];
            Log::warning("overall_grade was empty. Assigned a random grade '{$overallGrade}' for student {$student->id}.");
        }

        $data = [
            'student_id'            => $student->id,
            'overall_grade'         => $overallGrade,
            'agriculture_grade'     => UserImport::sanitizeData($row['agriculture_grade'] ?? null),
            'mathematics_grade'     => UserImport::sanitizeData($row['mathematics_grade'] ?? null),
            'english_grade'         => UserImport::sanitizeData($row['english_grade'] ?? null),
            'science_grade'         => UserImport::sanitizeData($row['science_grade'] ?? null),
            'social_studies_grade'  => UserImport::sanitizeData($row['social_studies_grade'] ?? null),
            'setswana_grade'        => UserImport::sanitizeData($row['setswana_grade'] ?? null),
            'capa_grade'            => UserImport::sanitizeData($row['capa_grade'] ?? null),
            'religious_and_moral_education_grade' => UserImport::sanitizeData($row['religious_and_moral_education_grade'] ?? null),
        ];

        try {
            PSLE::create($data);
            Log::info("PSLE grades saved for student {$student->id}");
        } catch (\Exception $e) {
            Log::error("Error saving PSLE grades for student {$student->id}: " . $e->getMessage());
        }
    }

    private function saveJceGrade(Student $student, array $row){
        $subjectAbbreviations = [
            'ov' => 'overall',
            'math' => 'mathematics',
            'eng' => 'english',
            'sci' => 'science',
            'set' => 'setswana',
            'dt' => 'design_and_technology',
            'he' => 'home_economics',
            'agr' => 'agriculture',
            'me' => 'moral_education',
            're' => 'religious_education',
            'mus' => 'music',
            'pe' => 'physical_education',
            'art' => 'art',
            'op' => 'office_procedures',
            'acc' => 'accounting',
            'fr' => 'french',
            'ss' => 'social_studies'
        ];

        $jceData = ['student_id' => $student->id];
        foreach ($subjectAbbreviations as $abbr => $fullName) {
            $grade = $this->sanitizeGrade($row[$abbr] ?? null);
            if (array_key_exists($abbr, $row)) {
                if (!is_null($grade) && preg_match('/^[A-U]$/i', $grade)) {
                    $jceData[$fullName] = strtoupper($grade);
                } else {
                    Log::warning("Invalid grade '{$grade}' for subject '{$fullName}' ('{$abbr}') and student {$student->id}. Skipping this grade.");
                }
            } else {
                Log::info("Column '{$abbr}' is missing for student {$student->id}. Skipping this grade.");
            }
        }

        if (count($jceData) > 1) {
            try {
                DB::transaction(function () use ($student, $jceData) {
                    JCE::updateOrCreate(
                        ['student_id' => $student->id],
                        $jceData
                    );
                });
                Log::info("JCE grades saved for student {$student->id}");
            } catch (\Exception $e) {
                Log::error("Error saving JCE grades for student {$student->id}: " . $e->getMessage());
            }
        } else {
            Log::warning("No valid JCE grades found for student {$student->id}. Skipping JCE record creation/update.");
        }
    }

    private function sanitizeGrade($grade){
        return UserImport::sanitizeData($grade);
    }

    private function resolveExternalExamImportType(?Grade $grade): ?string
    {
        if (!$grade) {
            return null;
        }

        $gradeLevel = trim((string) $grade->level);

        return match ($this->resolvedSchoolType) {
            SchoolSetup::TYPE_JUNIOR => $gradeLevel === SchoolSetup::LEVEL_JUNIOR ? 'psle' : null,
            SchoolSetup::TYPE_SENIOR => $gradeLevel === SchoolSetup::LEVEL_SENIOR ? 'jce' : null,
            SchoolSetup::TYPE_PRE_F3 => $gradeLevel === SchoolSetup::LEVEL_JUNIOR ? 'psle' : null,
            SchoolSetup::TYPE_JUNIOR_SENIOR => match ($gradeLevel) {
                SchoolSetup::LEVEL_JUNIOR => 'psle',
                SchoolSetup::LEVEL_SENIOR => 'jce',
                default => null,
            },
            SchoolSetup::TYPE_K12 => match ($gradeLevel) {
                SchoolSetup::LEVEL_JUNIOR => 'psle',
                SchoolSetup::LEVEL_SENIOR => 'jce',
                default => null,
            },
            default => null,
        };
    }

    public function rules(): array{
        return [
            '*.connect_id' => 'required',
            '*.first_name' => 'required',
            '*.last_name' => 'required',
            '*.gender' => 'required',
            '*.date_of_birth' => 'required',
            '*.nationality' => 'required',
            '*.id_number' => 'required|unique:students,id_number',
            '*.status' => 'required',
            '*.grade' => 'required',
        ];
    }

    public function customValidationMessages(){
        return [
            'connect_id.required' => 'The connect_id field is required.',
            'first_name.required' => 'The first_name field is required.',
            'last_name.required' => 'The last_name field is required.',
            'gender.required' => 'The gender field is required.',
            'date_of_birth.required' => 'The date_of_birth field is required.',
            'nationality.required' => 'The nationality field is required.',
            'id_number.required' => 'The id_number field is required.',
            'id_number.unique' => 'Duplicate id_number detected.',
            'status.required' => 'The status field is required.',
            'grade.required' => 'The grade field is required.',
        ];
    }

    public function customValidationAttributes(){
        return [
            'connect_id' => 'Connect ID',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'gender' => 'Gender',
            'date_of_birth' => 'Date of Birth',
            'nationality' => 'Nationality',
            'id_number' => 'ID Number',
            'status' => 'Status',
            'grade' => 'Grade',
        ];
    }

    private function forceFormatDateOfBirth($dateString){
        if (empty($dateString)) {
            return null;
        }
        try {
            if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $dateString)) {
                $parts = explode('/', $dateString);
                return "{$parts[2]}-{$parts[1]}-{$parts[0]}";
            }

            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateString)) {
                $date = Carbon::createFromFormat('Y-m-d', $dateString);
            } elseif (is_numeric($dateString)) {
                $date = ExcelDate::excelToDateTimeObject($dateString);
            } else {
                $date = Carbon::createFromFormat('d/m/Y', $dateString);
            }

            return $date->format('Y-m-d');
        } catch (\Exception $e) {
            Log::error("Date parsing error for {$dateString}: " . $e->getMessage());
            throw $e;
        }
    }

    private function formatName($name){
        if (empty($name)) {
            return null;
        }
        
        $name = strtolower(trim($name));
        $parts = explode(' ', $name);
        
        $formattedParts = array_map(function($part) {
            return ucfirst($part);
        }, $parts);
        return implode(' ', $formattedParts);
    }

    private function normalizeStudentStatus(?string $status): ?string
    {
        $normalized = strtolower(trim((string) UserImport::sanitizeData($status)));

        return match ($normalized) {
            'active', 'current' => Student::STATUS_CURRENT,
            'left' => Student::STATUS_LEFT,
            'suspended' => Student::STATUS_SUSPENDED,
            'graduated' => Student::STATUS_GRADUATED,
            '' => null,
            default => $this->formatName($normalized),
        };
    }

    private function klassCacheKey(?string $klassName, ?int $gradeId): string
    {
        return trim((string) $klassName) . '|' . (string) $gradeId;
    }

    public function registerEvents(): array{
        return [
            BeforeImport::class => function () {
                DB::beginTransaction();
                $this->closePreviousTerms();
            },
            AfterImport::class => function () {
                if ($this->rowsCount > 0 && count($this->failures()) == 0) {
                    DB::commit();
                } else {
                    DB::rollBack();
                }
                Cache::flush();
                Log::info("Total rows processed: {$this->rowsCount}");
            },
        ];
    }
}
