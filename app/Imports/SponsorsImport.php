<?php

namespace App\Imports;

use App\Helpers\CacheHelper;
use App\Models\Sponsor;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Events\AfterImport;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\Importable;

class SponsorsImport implements OnEachRow, WithHeadingRow, WithEvents, WithChunkReading, WithBatchInserts {
    use Importable;

    public $successfulImports = 0;
    public $skippedRows = 0;
    private $prefixes = ['71', '72', '73', '74', '76'];
    
    public function chunkSize(): int{
        return 100;
    }
    
    public function batchSize(): int{
        return 50;
    }

    public function onRow(Row $row) {
        $rowIndex = $row->getIndex();
        $rowData  = $row->toArray();

        $isEmptyRow = collect($rowData)->every(function ($value) {
            return empty($value);
        });
        
        if ($isEmptyRow) {
            $this->skippedRows++;
            return;
        }

        $validator = Validator::make($rowData, [
            'connect_id'    => 'required',
            'title'         => 'required',
            'first_name'    => 'required',
            'middle_name'   => 'nullable',
            'last_name'     => 'required',
            'email'         => 'nullable|email',
            'gender'        => 'required',
            'date_of_birth' => 'required',
            'nationality'   => 'required',
            'status'        => 'required',
            'id_number'     => 'required',
        ], [
            'connect_id.required'    => 'The connect_id field is required.',
            'title.required'         => 'The title field is required.',
            'first_name.required'    => 'The first_name field is required.',
            'last_name.required'     => 'The last_name field is required.',
            'gender.required'        => 'The gender field is required.',
            'date_of_birth.required' => 'The date_of_birth field is required.',
            'nationality.required'   => 'The nationality field is required.',
            'status.required'        => 'The status field is required.',
            'id_number.required'     => 'The id_number field is required.',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            $errorMessage = "Validation error on Excel row {$rowIndex}: " . implode(', ', $errors);
            Log::error($errorMessage);
            throw new \Exception($errorMessage);
        }

        try {
            $email = $rowData['email'] ?? null;
            if (empty($email)) {
                $firstName = self::sanitizeData($rowData['first_name'] ?? '');
                $lastName = self::sanitizeData($rowData['last_name'] ?? '');
                $email = $this->generateEmail($firstName, $lastName);
            } else {
                $email = strtolower(self::sanitizeData($email, 'email'));
            }
            
            $phone = $rowData['phone'] ?? null;
            if (empty($phone)) {
                $phone = $this->generatePhoneNumber();
            } else {
                $phone = self::sanitizeData($phone);
            }

            Sponsor::create([
                'connect_id'    => self::sanitizeData($rowData['connect_id'] ?? null),
                'title'         => self::sanitizeData($rowData['title'] ?? null),
                'first_name'    => $this->formatName(self::sanitizeData($rowData['first_name'] ?? null)),
                'middle_name'   => $this->formatName(self::sanitizeData($rowData['middle_name'] ?? null)),
                'last_name'     => $this->formatName(self::sanitizeData($rowData['last_name'] ?? null)),
                'email'         => $email,
                'gender'        => self::sanitizeData($rowData['gender'] ?? null),
                'date_of_birth' => $this->forceFormatDateOfBirth($rowData['date_of_birth'] ?? null),
                'nationality'   => $this->formatName($rowData['nationality'] ?? null),
                'relation'      => $this->formatName($rowData['relation'] ?? null),
                'status'        => self::sanitizeData($rowData['status'] ?? null),
                'id_number'     => self::sanitizeData($rowData['id_number'] ?? null),
                'phone'         => $phone,
                'profession'    => $this->formatName($rowData['profession'] ?? null),
                'work_place'    => $this->formatName($rowData['work_place'] ?? null),
                'year'          => self::sanitizeData($rowData['year'] ?? null),
            ]);
            $this->successfulImports++;
        } catch (\Exception $e) {
            $errorMessage = "Error processing Excel row {$rowIndex}: " . $e->getMessage();
            Log::error($errorMessage);
            throw new \Exception($errorMessage);
        }
    }

    private function generateEmail($firstName, $lastName) {
        $firstName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $firstName));
        $lastName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $lastName));
        
        if (empty($firstName)) $firstName = 'user';
        if (empty($lastName)) $lastName = 'account';
        
        $domains = ['gmail.com', 'yahoo.com', 'outlook.com', 'hotmail.com'];
        $randomDomain = $domains[array_rand($domains)];
        
        $options = [
            "{$firstName}.{$lastName}@{$randomDomain}",
            "{$firstName}{$lastName}@{$randomDomain}",
            "{$firstName}.{$lastName}." . rand(1, 999) . "@{$randomDomain}",
            "{$firstName}{$lastName}" . rand(1, 999) . "@{$randomDomain}",
            substr($firstName, 0, 1) . "{$lastName}@{$randomDomain}",
        ];
        
        foreach ($options as $email) {
            if (!Sponsor::where('email', $email)->exists()) {
                return $email;
            }
        }
        return "{$firstName}.{$lastName}." . substr(Str::uuid(), 0, 8) . "@{$randomDomain}";
    }
    
    private function generatePhoneNumber() {
        $prefix = $this->prefixes[array_rand($this->prefixes)];
        $suffix = '';
        
        for ($i = 0; $i < 6; $i++) {
            $suffix .= mt_rand(0, 9);
        }
        
        return $prefix . $suffix;
    }

    private function formatName($name) {
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

    public function registerEvents(): array {
        return [
            AfterImport::class => function (AfterImport $event) {
                Cache::flush();
                Log::info("Successful imports: {$this->successfulImports}");
                Log::info("Skipped rows: {$this->skippedRows}");
            },
        ];
    }

    private function forceFormatDateOfBirth($dateString) {
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

    public static function sanitizeData($value, $type = 'text') {
        if ($type === 'email') {
            return preg_replace('/[^a-zA-Z0-9\.\-_@]/', '', $value);
        }
        return preg_replace('/[^a-zA-Z0-9\s\\\\]/', '', $value);
    }
}
