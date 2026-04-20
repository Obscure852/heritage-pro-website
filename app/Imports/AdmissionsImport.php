<?php

namespace App\Imports;

use App\Helpers\CacheHelper;
use App\Models\Sponsor;
use App\Models\Admission;
use App\Models\Term;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\BeforeImport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;

class AdmissionsImport implements ToModel, WithHeadingRow, WithEvents, WithValidation, SkipsOnFailure{
    use Importable, SkipsFailures;

    public $rowsCount = 0;
    public $successfulImports = 0;
    public $skippedRows = 0;
    private $term;

    public function __construct(private $termId){
        $this->term = Term::find($termId);
    }

    public function model(array $row){
        if ($this->isEmptyRow($row)) {
            $this->skippedRows++;
            return null;
        }

        $this->rowsCount++;

        try {
            $sponsorId = $this->getSponsorId($row);
            $admission = $this->createAdmission($row, $sponsorId);
            $this->successfulImports++;
            return $admission;
        } catch (\Exception $e) {
            Log::error("Error importing admission: " . $e->getMessage());
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

    private function isEmptyRow(array $row){
        return collect($row)->every(fn($value) => empty($value) || is_null($value));
    }

    private function getSponsorId(array $row){
        $sponsor = Sponsor::where('connect_id', UserImport::sanitizeData($row['connect_id']))->first();
        return $sponsor?->id;
    }

    private function createAdmission(array $row, ?int $sponsorId){
        return Admission::create([
            'sponsor_id' => $sponsorId,
            'first_name' => $this->formatName(UserImport::sanitizeData($row['first_name'])),
            'last_name' => $this->formatName(UserImport::sanitizeData($row['last_name'])),
            'middle_name' => isset($row['middle_name']) ? $this->formatName(UserImport::sanitizeData($row['middle_name'])) : null,
            'gender' => UserImport::sanitizeData($row['gender']),
            'date_of_birth' => self::formatDateOfBirth($row['date_of_birth']),
            'nationality' => $this->formatName(UserImport::sanitizeData($row['nationality'])),
            'phone' => UserImport::sanitizeData($row['phone']),
            'id_number' => UserImport::sanitizeData($row['id_number']),
            'grade_applying_for' => UserImport::sanitizeData($row['grade']),
            'academic_year_applying_for' => isset($row['year']) ? UserImport::sanitizeData($row['year']) : $this->term->year,
            'application_date' => now(),
            'status' => UserImport::sanitizeData($row['status']),
            'term_id' => $this->term->id,
            'year' => $this->term->year,
        ]);
    }

    public function rules(): array{
        return [
            '*.connect_id' => 'required',
            '*.first_name' => 'required',
            '*.last_name' => 'required',
            '*.gender' => 'required',
            '*.date_of_birth' => 'required|date_format:d/m/Y',
            '*.nationality' => 'required',
            '*.phone' => 'required',
            '*.id_number' => 'required|unique:admissions,id_number',
            '*.grade' => 'required',
            '*.status' => 'required',
        ];
    }

    public function customValidationMessages(){
        return [
            'connect_id.required' => 'The connect_id field is required.',
            'first_name.required' => 'The first_name field is required.',
            'last_name.required' => 'The last_name field is required.',
            'gender.required' => 'The gender field is required.',
            'date_of_birth.required' => 'The date_of_birth field is required.',
            'date_of_birth.date_format' => 'The date_of_birth must be in the format dd/mm/YYYY.',
            'nationality.required' => 'The nationality field is required.',
            'phone.required' => 'The phone field is required.',
            'id_number.required' => 'The id_number field is required.',
            'id_number.unique' => 'Duplicate id_number detected.',
            'grade.required' => 'The grade field is required.',
            'status.required' => 'The status field is required.',
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
            'phone' => 'Phone',
            'id_number' => 'ID Number',
            'grade' => 'Grade Applying For',
            'status' => 'Status',
        ];
    }

    public static function formatDateOfBirth($dateString){
        try {
            if (is_numeric($dateString)) {
                $date = ExcelDate::excelToDateTimeObject($dateString);
            } else {
                $date = Carbon::createFromFormat('d/m/Y', trim($dateString));
            }
            return $date->format('Y-m-d');
        } catch (\Exception $e) {
            Log::error("Date parsing error for {$dateString}: " . $e->getMessage());
            return null;
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

    public function registerEvents(): array{
        return [
            BeforeImport::class => function () {
                DB::beginTransaction();
            },
            AfterImport::class => function () {
                if ($this->rowsCount > 0 && count($this->failures()) == 0) {
                    DB::commit();
                } else {
                    DB::rollBack();
                }
                CacheHelper::forgetCurrentTermAdmissions();
                CacheHelper::forgetSponsors();
                CacheHelper::forgetNationalities();
                Log::info("Total rows processed: {$this->rowsCount}");
                Log::info("Successful imports: {$this->successfulImports}");
                Log::info("Skipped rows: {$this->skippedRows}");
            },
        ];
    }
}
