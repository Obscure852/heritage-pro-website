<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class StaffProfileSetting extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'key',
        'value',
        'description',
        'updated_by',
    ];

    protected $casts = [
        'value' => 'array',
        'updated_at' => 'datetime',
    ];

    public const KEY_ENABLED = 'force_profile_update_enabled';
    public const KEY_SECTIONS = 'force_profile_update_sections';

    public const SECTIONS = [
        'basic_info' => [
            'label' => 'Basic Information',
            'description' => 'Name, date of birth, ID number, email, nationality',
            'fields' => ['firstname', 'lastname', 'date_of_birth', 'id_number', 'email', 'nationality'],
        ],
        'employment_details' => [
            'label' => 'Employment Details',
            'description' => 'Payroll number, DPSM file number, date of appointment, earning band',
            'fields' => ['personal_payroll_number', 'dpsm_personal_file_number', 'date_of_appointment', 'earning_band'],
        ],
        'qualifications' => [
            'label' => 'Qualifications',
            'description' => 'At least 1 qualification record',
            'type' => 'relationship',
        ],
        'work_history' => [
            'label' => 'Work History',
            'description' => 'At least 1 work history entry',
            'type' => 'relationship',
        ],
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model) {
            $model->updated_at = $model->updated_at ?? now();
        });

        static::updating(function (self $model) {
            $model->updated_at = now();
        });
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();

        if (!$setting) {
            return $default;
        }

        return $setting->value ?? $default;
    }

    public static function set(string $key, $value, ?int $userId = null): self
    {
        $setting = static::firstOrNew(['key' => $key]);
        $setting->value = $value;
        $setting->updated_by = $userId;
        $setting->updated_at = now();
        $setting->save();

        Cache::forget('force_profile_update_enabled');
        Cache::forget('force_profile_required_sections');

        return $setting;
    }

    public static function isForceUpdateEnabled(): bool
    {
        return (bool) static::get(self::KEY_ENABLED, false);
    }

    public static function getRequiredSections(): array
    {
        $sections = Cache::remember('force_profile_required_sections', 60, function () {
            return static::get(self::KEY_SECTIONS, ['basic_info']);
        });

        return is_array($sections) ? $sections : ['basic_info'];
    }

    public static function getIncompleteItems(User $user): array
    {
        $requiredSections = static::getRequiredSections();
        $missingFields = [];
        $missingSections = [];

        foreach ($requiredSections as $sectionKey) {
            $section = self::SECTIONS[$sectionKey] ?? null;

            if (!$section) {
                continue;
            }

            if (isset($section['type']) && $section['type'] === 'relationship') {
                if ($sectionKey === 'qualifications' && $user->qualifications()->count() === 0) {
                    $missingSections[] = $sectionKey;
                } elseif ($sectionKey === 'work_history' && $user->workHistory()->count() === 0) {
                    $missingSections[] = $sectionKey;
                }
            } elseif (isset($section['fields'])) {
                foreach ($section['fields'] as $field) {
                    $value = $user->getAttribute($field);
                    if ($value === null || $value === '') {
                        $missingFields[] = $field;
                    }
                }
            }
        }

        return [
            'missing_fields' => $missingFields,
            'missing_sections' => $missingSections,
        ];
    }
}
