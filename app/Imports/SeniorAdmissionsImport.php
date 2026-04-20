<?php

namespace App\Imports;

use App\Models\Admission;
use App\Models\SeniorAdmissionAcademic;
use App\Models\Sponsor;
use App\Models\Term;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\RemembersRowNumber;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\Failure;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class SeniorAdmissionsImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure, WithChunkReading
{
    use Importable;
    use RemembersRowNumber;
    use SkipsFailures;

    private const OVERALL_GRADES = ['A', 'B', 'C', 'D', 'M'];
    private const SUBJECT_GRADES = ['A', 'B', 'C', 'D', 'E', 'U'];
    private const SUBJECT_COLUMNS = [
        'english',
        'setswana',
        'science',
        'mathematics',
        'agriculture',
        'social_studies',
        'moral_education',
        'design_and_technology',
        'home_economics',
        'office_procedures',
        'accounting',
        'french',
        'art',
        'music',
        'physical_education',
        'religious_education',
        'private_agriculture',
    ];

    public int $rowsCount = 0;
    public int $successfulImports = 0;
    public int $skippedRows = 0;
    public array $skippedReasons = [];

    private Term $term;
    private array $sponsors;

    public function __construct(
        private readonly int $termId,
        private readonly ?int $userId = null,
    ) {
        $this->term = Term::query()->findOrFail($this->termId);
        $this->sponsors = Sponsor::query()
            ->pluck('id', 'connect_id')
            ->mapWithKeys(fn($id, $connectId) => [(string) $connectId => $id])
            ->all();
    }

    public static function templateHeaders(): array {
        return [
            'connect_id',
            'firstname',
            'lastname',
            'gender',
            'nationality',
            'date_of_birth',
            'status',
            'grade',
            'grade_applying_for',
            ...self::SUBJECT_COLUMNS,
        ];
    }

    public function chunkSize(): int {
        return 100;
    }

    public function prepareForValidation($row, $index) {
        $row['connect_id'] = $this->normalizeConnectId($row['connect_id'] ?? null);
        $row['firstname'] = $this->sanitizeText($row['firstname'] ?? null);
        $row['lastname'] = $this->sanitizeText($row['lastname'] ?? null);
        $row['gender'] = strtoupper($this->sanitizeText($row['gender'] ?? null));
        $row['nationality'] = $this->sanitizeText($row['nationality'] ?? null);
        $row['date_of_birth'] = $this->sanitizeText($row['date_of_birth'] ?? null);
        $row['status'] = $this->sanitizeText($row['status'] ?? null);
        $row['grade'] = strtoupper($this->sanitizeText($row['grade'] ?? null));
        $row['grade_applying_for'] = strtoupper($this->sanitizeText($row['grade_applying_for'] ?? null));

        foreach (self::SUBJECT_COLUMNS as $column) {
            $row[$column] = strtoupper($this->sanitizeText($row[$column] ?? null));
        }

        return $row;
    }

    public function rules(): array {
        $rules = [
            '*.connect_id' => ['required'],
            '*.firstname' => ['required', 'string'],
            '*.lastname' => ['required', 'string'],
            '*.gender' => ['required', Rule::in(['M', 'F'])],
            '*.nationality' => ['required', 'string'],
            '*.date_of_birth' => ['required', function ($attribute, $value, $fail) {
                if (!$this->canParseDate($value)) {
                    $fail('The date_of_birth must be a valid Excel date or in DD/MM/YYYY format.');
                }
            }],
            '*.status' => ['required', 'string'],
            '*.grade' => ['required', Rule::in(self::OVERALL_GRADES)],
            '*.grade_applying_for' => ['required', Rule::in(['F4'])],
        ];

        foreach (self::SUBJECT_COLUMNS as $column) {
            $rules["*.{$column}"] = ['nullable', Rule::in(self::SUBJECT_GRADES)];
        }

        return $rules;
    }

    public function customValidationAttributes(): array {
        return [
            'connect_id' => 'Connect ID',
            'firstname' => 'First Name',
            'lastname' => 'Last Name',
            'date_of_birth' => 'Date of Birth',
            'grade_applying_for' => 'Grade Applying For',
        ];
    }

    public function model(array $row) {
        if ($this->isEmptyRow($row)) {
            $this->skippedRows++;
            return null;
        }

        $this->rowsCount++;
        $rowNumber = $this->getRowNumber() ?? ($this->rowsCount + 1);

        $sponsorId = $this->sponsors[$row['connect_id']] ?? null;

        $generatedIdNumber = $this->generateImportedIdNumber($row);
        if (Admission::query()
            ->where('term_id', $this->term->id)
            ->where('id_number', $generatedIdNumber)
            ->exists()
        ) {
            $this->skippedRows++;
            $this->skippedReasons[] = "Row {$rowNumber}: duplicate admission for the selected term skipped.";
            return null;
        }

        try {
            $admission = DB::transaction(function () use ($row, $sponsorId, $generatedIdNumber) {
                $admission = Admission::create([
                    'sponsor_id' => $sponsorId,
                    'connect_id' => $row['connect_id'],
                    'first_name' => $this->formatName($row['firstname']),
                    'last_name' => $this->formatName($row['lastname']),
                    'middle_name' => null,
                    'gender' => $row['gender'],
                    'date_of_birth' => $this->formatDateOfBirth($row['date_of_birth']),
                    'nationality' => $this->formatName($row['nationality']),
                    'phone' => null,
                    'id_number' => $generatedIdNumber,
                    'term_id' => $this->term->id,
                    'grade_applying_for' => 'F4',
                    'year' => $this->term->year,
                    'application_date' => now()->toDateString(),
                    'status' => $this->formatName($row['status']),
                    'last_updated_by' => $this->userId,
                ]);

                SeniorAdmissionAcademic::updateOrCreate(
                    ['admission_id' => $admission->id],
                    [
                        'overall' => $row['grade'],
                        'english' => $row['english'] ?: null,
                        'setswana' => $row['setswana'] ?: null,
                        'science' => $row['science'] ?: null,
                        'mathematics' => $row['mathematics'] ?: null,
                        'agriculture' => $row['agriculture'] ?: null,
                        'social_studies' => $row['social_studies'] ?: null,
                        'moral_education' => $row['moral_education'] ?: null,
                        'design_and_technology' => $row['design_and_technology'] ?: null,
                        'home_economics' => $row['home_economics'] ?: null,
                        'office_procedures' => $row['office_procedures'] ?: null,
                        'accounting' => $row['accounting'] ?: null,
                        'french' => $row['french'] ?: null,
                        'art' => $row['art'] ?: null,
                        'music' => $row['music'] ?: null,
                        'physical_education' => $row['physical_education'] ?: null,
                        'religious_education' => $row['religious_education'] ?: null,
                        'private_agriculture' => $row['private_agriculture'] ?: null,
                    ]
                );

                return $admission;
            });

            $this->successfulImports++;

            return $admission;
        } catch (\Throwable $e) {
            Log::error('Error importing senior admission row', [
                'row' => $rowNumber,
                'message' => $e->getMessage(),
            ]);

            $this->skippedRows++;
            $this->onFailure(new Failure(
                $rowNumber,
                'import',
                [$e->getMessage()],
                $row
            ));

            return null;
        }
    }

    private function isEmptyRow(array $row): bool {
        return collect($row)
            ->filter(fn($value, $key) => is_string($key))
            ->every(fn($value) => $value === null || trim((string) $value) === '');
    }

    private function normalizeConnectId($value): ?string {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (string) (int) round((float) $value);
        }

        $value = trim((string) $value);
        if (preg_match('/^\d+\.0+$/', $value)) {
            $value = strstr($value, '.', true);
        }

        $digits = preg_replace('/\D/', '', $value);

        return $digits !== '' ? $digits : null;
    }

    private function sanitizeText($value): ?string {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function formatName(?string $value): ?string {
        if (!$value) {
            return null;
        }

        return collect(preg_split('/\s+/', strtolower($value)) ?: [])
            ->filter()
            ->map(fn($part) => ucfirst($part))
            ->implode(' ');
    }

    private function canParseDate($value): bool {
        try {
            $this->formatDateOfBirth($value);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    private function formatDateOfBirth($value): string {
        if (is_numeric($value)) {
            return Carbon::instance(ExcelDate::excelToDateTimeObject($value))->format('Y-m-d');
        }

        $value = trim((string) $value);

        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $value)) {
            return Carbon::createFromFormat('d/m/Y', $value)->format('Y-m-d');
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return Carbon::createFromFormat('Y-m-d', $value)->format('Y-m-d');
        }

        throw new \InvalidArgumentException("Unsupported date value [{$value}]");
    }

    private function generateImportedIdNumber(array $row): string {
        $source = implode('|', [
            $row['connect_id'],
            strtolower($row['firstname'] ?? ''),
            strtolower($row['lastname'] ?? ''),
            $this->formatDateOfBirth($row['date_of_birth']),
        ]);

        return sprintf(
            'F4IMP-%s-%s',
            $row['connect_id'],
            strtoupper(substr(sha1($source), 0, 12))
        );
    }
}
