<?php

namespace App\Imports;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetLog;
use App\Models\Contact;
use App\Models\Venue;
use App\Services\Contacts\ContactManagementService;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\BeforeImport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Carbon\Carbon;

class AssetsImport implements ToModel, WithHeadingRow, WithEvents, WithValidation, SkipsOnFailure{
    use Importable, SkipsFailures;

    public $rowsCount = 0;
    public $createdCategories = [];
    public $createdContacts = [];
    private $options;

    private $allowedStatuses = ['Available', 'Assigned', 'In Maintenance', 'Disposed'];
    private $allowedConditions = ['New', 'Good', 'Fair', 'Poor'];

    public function __construct(array $options = []){
        $this->options = array_merge([
            'create_missing_categories' => true,
            'create_missing_contacts' => true,
        ], $options);
    }

    public function model(array $row){
        if ($this->isEmptyRow($row)) {
            return null;
        }

        try {
            $this->rowsCount++;
            if (Asset::where('asset_code', $this->sanitizeData($row['asset_code']))->exists()) {
                Log::warning("Duplicate asset code skipped: " . $row['asset_code'] . " at row {$this->rowsCount}");
                return null;
            }

            $categoryId = $this->getOrCreateCategory($row);
            if (!$categoryId) {
                $failure = new Failure(
                    $this->rowsCount,
                    'category',
                    ["Category not found and cannot be created"],
                    $row
                );
                $this->onFailure($failure);
                return null;
            }

            $contactId = $this->getOrCreateContact($row);
            $venueId = $this->getVenue($row);

            $asset = $this->createAsset($row, $categoryId, $contactId, $venueId);
            AssetLog::createLog(
                $asset->id,
                'create',
                'Asset created via import',
                ['import_row' => $this->rowsCount],
                auth()->id()
            );

            return $asset;

        } catch (\Exception $e) {
            Log::error("Error importing asset at row {$this->rowsCount}: " . $e->getMessage());
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

    private function createAsset(array $row, int $categoryId, ?int $contactId, ?int $venueId){
        return Asset::create([
            'name' => $this->formatName($row['asset_name']),
            'asset_code' => $this->sanitizeData($row['asset_code']),
            'category_id' => $categoryId,
            'contact_id' => $contactId,
            'venue_id' => $venueId,
            'status' => $this->validateStatus($row['status'] ?? 'Available'),
            'make' => $this->sanitizeData($row['manufacturer']),
            'model' => $this->sanitizeData($row['model']),
            'purchase_date' => $this->parseDate($row['purchase_date']),
            'purchase_price' => $this->parseNumber($row['purchase_price']),
            'current_value' => $this->parseNumber($row['current_value']),
            'warranty_expiry' => $this->parseDate($row['warranty_expiry']),
            'expected_lifespan' => $this->parseNumber($row['expected_lifespan']),
            'invoice_number' => $this->sanitizeData($row['invoice_number']),
            'condition' => $this->validateCondition($row['condition'] ?? 'Good'),
            'specifications' => $this->sanitizeData($row['specifications']),
            'notes' => $this->sanitizeData($row['notes']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function getOrCreateCategory(array $row){
        $categoryName = $this->sanitizeData($row['category']);
        if (empty($categoryName)) {
            return null;
        }

        $category = AssetCategory::where('name', $categoryName)->first();

        if (!$category && $this->options['create_missing_categories']) {
            try {
                $category = AssetCategory::create([
                    'name' => $categoryName,
                    'code' => $this->generateCategoryCode($categoryName),
                    'description' => 'Auto-created during import',
                    'is_active' => true,
                ]);

                if (!in_array($categoryName, $this->createdCategories)) {
                    $this->createdCategories[] = $categoryName;
                }

                Log::info("Created new category: {$categoryName}");
            } catch (\Exception $e) {
                Log::error("Error creating category {$categoryName}: " . $e->getMessage());
                return null;
            }
        }

        return $category?->id;
    }

    private function getOrCreateContact(array $row){
        $contactName = $this->sanitizeData($row['business_contact'] ?? $row['vendor'] ?? null);
        if (empty($contactName)) {
            return null;
        }

        $contact = Contact::query()->where('name', $contactName)->first();

        if (!$contact && ($this->options['create_missing_contacts'] ?? $this->options['create_missing_vendors'] ?? false)) {
            try {
                $contact = app(ContactManagementService::class)->resolveImportContactByName($contactName);

                if (!in_array($contactName, $this->createdContacts, true)) {
                    $this->createdContacts[] = $contactName;
                }

                Log::info("Created new business contact: {$contactName}");
            } catch (\Exception $e) {
                Log::error("Error creating business contact {$contactName}: " . $e->getMessage());
                return null;
            }
        }

        return $contact?->id;
    }

    private function getVenue(array $row){
        $locationName = $this->sanitizeData($row['location']);
        if (empty($locationName)) {
            return null;
        }

        $venue = Venue::where('name', $locationName)->first();
        return $venue?->id;
    }

    private function validateStatus($status){
        $status = $this->sanitizeData($status);
        return in_array($status, $this->allowedStatuses) ? $status : 'Available';
    }

    private function validateCondition($condition){
        $condition = $this->sanitizeData($condition);
        return in_array($condition, $this->allowedConditions) ? $condition : 'Good';
    }

    private function parseDate($dateString){
        if (empty($dateString)) {
            return null;
        }

        try {
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateString)) {
                return $dateString;
            }
            
            if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $dateString)) {
                $parts = explode('/', $dateString);
                return "{$parts[2]}-{$parts[1]}-{$parts[0]}";
            }

            $date = Carbon::parse($dateString);
            return $date->format('Y-m-d');
        } catch (\Exception $e) {
            Log::warning("Could not parse date: {$dateString}");
            return null;
        }
    }

    private function parseNumber($value){
        if (empty($value)) {
            return null;
        }

        $value = preg_replace('/[^\d.-]/', '', $value);
        
        return is_numeric($value) ? (float)$value : null;
    }

    private function sanitizeData($data){
        if (is_null($data)) {
            return null;
        }
        
        return trim($data);
    }

    private function formatName($name){
        if (empty($name)) {
            return null;
        }
        
        return trim($name);
    }

    private function generateCategoryCode($name){
        $code = strtoupper(Str::slug($name, '_'));
        $code = substr($code, 0, 10);
        
        $counter = 1;
        $originalCode = $code;
        while (AssetCategory::where('code', $code)->exists()) {
            $code = $originalCode . '_' . $counter;
            $counter++;
        }
        
        return $code;
    }

    public function rules(): array{
        return [
            '*.asset_name' => 'required|string|max:255',
            '*.asset_code' => 'required|string|max:255|unique:assets,asset_code',
            '*.category' => 'required|string|max:255',
            '*.business_contact' => 'nullable|string|max:255',
            '*.vendor' => 'nullable|string|max:255',
            '*.location' => 'nullable|string|max:255',
            '*.status' => 'nullable|in:Available,Assigned,In Maintenance,Disposed',
            '*.manufacturer' => 'nullable|string|max:255',
            '*.model' => 'nullable|string|max:255',
            '*.purchase_date' => 'nullable|date',
            '*.purchase_price' => 'nullable|numeric|min:0',
            '*.current_value' => 'nullable|numeric|min:0',
            '*.warranty_expiry' => 'nullable|date',
            '*.expected_lifespan' => 'nullable|integer|min:1',
            '*.invoice_number' => 'nullable|string|max:255',
            '*.condition' => 'nullable|in:New,Good,Fair,Poor',
            '*.specifications' => 'nullable|string',
            '*.notes' => 'nullable|string',
        ];
    }

    public function customValidationMessages(){
        return [
            'asset_name.required' => 'The asset name field is required.',
            'asset_code.required' => 'The asset code field is required.',
            'asset_code.unique' => 'Duplicate asset code detected.',
            'category.required' => 'The category field is required.',
            'status.in' => 'Invalid status. Allowed values: ' . implode(', ', $this->allowedStatuses),
            'condition.in' => 'Invalid condition. Allowed values: ' . implode(', ', $this->allowedConditions),
            'purchase_price.numeric' => 'Purchase price must be a number.',
            'current_value.numeric' => 'Current value must be a number.',
            'expected_lifespan.integer' => 'Expected lifespan must be a whole number.',
            'expected_lifespan.min' => 'Expected lifespan must be at least 1 month.',
        ];
    }

    public function customValidationAttributes(){
        return [
            'asset_name' => 'Asset Name',
            'asset_code' => 'Asset Code',
            'category' => 'Category',
            'business_contact' => 'Business Contact',
            'vendor' => 'Business Contact',
            'location' => 'Location',
            'status' => 'Status',
            'manufacturer' => 'Manufacturer',
            'model' => 'Model',
            'purchase_date' => 'Purchase Date',
            'purchase_price' => 'Purchase Price',
            'current_value' => 'Current Value',
            'warranty_expiry' => 'Warranty Expiry',
            'expected_lifespan' => 'Expected Lifespan',
            'invoice_number' => 'Invoice Number',
            'condition' => 'Condition',
            'specifications' => 'Specifications',
            'notes' => 'Notes',
        ];
    }

    public function registerEvents(): array{
        return [
            BeforeImport::class => function () {
                DB::beginTransaction();
            },
            AfterImport::class => function () {
                if ($this->rowsCount > 0 && count($this->failures()) == 0) {
                    DB::commit();
                    Log::info("Asset import completed successfully. Rows processed: {$this->rowsCount}");
                } else {
                    DB::rollBack();
                    Log::warning("Asset import rolled back due to failures. Rows processed: {$this->rowsCount}");
                }
            },
        ];
    }
}
