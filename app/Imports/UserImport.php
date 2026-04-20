<?php

namespace App\Imports;

use App\Helpers\CacheHelper;
use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Events\AfterImport;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Illuminate\Support\Str;

class UserImport implements ToModel, WithHeadingRow, WithEvents, WithValidation, SkipsOnFailure{
    use Importable, SkipsFailures;

    public $rowsCount = 0;
    public $successfulImports = 0;
    public $skippedRows = 0;

    public function model(array $row){
        $isEmptyRow = collect($row)->every(function ($value) {
            return empty($value) || is_null($value);
        });

        if ($isEmptyRow) {
            $this->skippedRows++;
            return null;
        }

        $this->rowsCount++;
        $randomPassword = self::generateComplexPassword(8);

        try {
            $user = new User([
                'firstname'     => $this->formatName(self::sanitizeData($row['firstname'])),
                'middlename'    => isset($row['middlename']) ? $this->formatName(self::sanitizeData($row['middlename'])) : null,
                'lastname'      => $this->formatName(self::sanitizeData($row['lastname'])),
                'email'         => strtolower(self::sanitizeData($row['email'], 'email')),
                'date_of_birth' => $this->forceFormatDateOfBirth($row['date_of_birth'] ?? null),
                'gender'        => self::sanitizeData($row['gender']),
                'position'      => $this->formatName(self::sanitizeData($row['position'])),
                'area_of_work'  => $this->formatName($row['area_of_work'] ?? null),
                'phone'         => isset($row['phone']) ? trim(self::sanitizeData($row['phone'])) : null,
                'id_number'     => trim(self::sanitizeData($row['id_number'])),
                'nationality'   => $this->formatName($row['nationality'] ?? null),
                'city'          => isset($row['city']) ? $this->formatName($row['city'] ?? null) : null,
                'address'       => isset($row['address']) ? $this->formatName($row['address'] ?? null) : null,
                'active'        => isset($row['active']) ? ucfirst(strtolower(trim(self::sanitizeData($row['active'])))) : true,
                'status'        => $this->formatName(self::sanitizeData($row['status'] ?? null)),
                'username'      => isset($row['lastname']) ? strtolower(trim(self::sanitizeData($row['lastname']))) : null,
                'password'      => Hash::make($randomPassword),
                'year'          => isset($row['year']) ? self::sanitizeData($row['year']) : null,
            ]);

            $this->successfulImports++;
            return $user;
        } catch (\Exception $e) {
            Log::error("Error importing user: " . $e->getMessage());
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

    private function formatName($name){
        $name = strtolower(trim($name));
        $parts = explode(' ', $name);
        $formattedParts = array_map(function($part) {
            return ucfirst($part);
        }, $parts);
        return implode(' ', $formattedParts);
    }

    public function rules(): array{
        return [
            '*.firstname'      => 'required',
            '*.lastname'       => 'required',
            '*.email'          => 'required|email|unique:users,email',
            '*.date_of_birth'  => 'required',
            '*.gender'         => 'required',
            '*.position'       => 'required',
            '*.area_of_work'   => 'required',
            '*.id_number'      => 'required|unique:users,id_number',
            '*.nationality'    => 'required',
            '*.status'         => 'required',
        ];
    }

    public function customValidationMessages(){
        return [
            'firstname.required'     => 'The firstname field is required.',
            'lastname.required'      => 'The lastname field is required.',
            'email.required'         => 'The email field is required.',
            'email.email'            => 'The email must be a valid email address.',
            'email.unique'           => 'The email has already been taken.',
            'date_of_birth.required' => 'The date_of_birth field is required.',
            'date_of_birth.date_format' => 'The date_of_birth must be in the format YYYY-MM-DD.',
            'gender.required'        => 'The gender field is required.',
            'position.required'      => 'The position field is required.',
            'area_of_work.required'  => 'The area_of_work field is required.',
            'id_number.required'     => 'The id_number field is required.',
            'id_number.unique'       => 'The id_number has already been taken.',
            'nationality.required'   => 'The nationality field is required.',
            'status.required'        => 'The status field is required.',
        ];
    }

    public function registerEvents(): array{
        return [
            AfterImport::class => function (AfterImport $event) {
                Cache::flush();
                Log::info("Total rows processed: {$this->rowsCount}");
                Log::info("Successful imports: {$this->successfulImports}");
                Log::info("Skipped rows: {$this->skippedRows}");
            },
        ];
    }

    private function forceFormatDateOfBirth($dateString){
        try {
            if (empty($dateString)) {
                return null;
            }

            if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $dateString)) {
                $parts = explode('/', $dateString);
                return "{$parts[2]}-{$parts[1]}-{$parts[0]}";
            }

            if (is_numeric($dateString)) {
                $date = ExcelDate::excelToDateTimeObject($dateString);
            } else {
                $date = Carbon::createFromFormat('d/m/Y', $dateString);
            }
            return $date->format('Y-m-d');
        } catch (\Exception $e) {
            Log::error("Date parsing error for {$dateString}: " . $e->getMessage());
            return null;
        }
    }

    public static function sanitizeData($value, $type = 'text'){
        if ($type == 'email') {
            return preg_replace('/[^a-zA-Z0-9\.\-_@]/', '', $value);
        } else {
            return preg_replace('/[^a-zA-Z0-9\s\\\\]/', '', $value);
        }
    }

    public static function generateComplexPassword($length = 8){
        $password = '';
        $password .= Str::random(2);
        $password .= chr(rand(65, 90));
        $password .= chr(rand(97, 122));
        $password .= rand(0, 9);
        while (strlen($password) < $length) {
            $password .= Str::random(1);
        }
        return str_shuffle($password);
    }
}
