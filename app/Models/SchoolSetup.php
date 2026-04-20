<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Log;

class SchoolSetup extends Model{
    use HasFactory, SoftDeletes;

    public const TYPE_PRIMARY = 'Primary';
    public const TYPE_JUNIOR = 'Junior';
    public const TYPE_SENIOR = 'Senior';
    public const TYPE_PRE_F3 = 'PRE_F3';
    public const TYPE_JUNIOR_SENIOR = 'JUNIOR_SENIOR';
    public const TYPE_K12 = 'K12';
    public const TYPE_UNIFIED = 'Unified';

    public const LEVEL_PRE_PRIMARY = 'Pre-primary';
    public const LEVEL_PRIMARY = 'Primary';
    public const LEVEL_JUNIOR = 'Junior';
    public const LEVEL_SENIOR = 'Senior';

    protected $table = 'school_setup';
    protected $fillable = [
        'school_name',
        'school_id',
        'slogan',
        'telephone',
        'fax',
        'email_address',
        'physical_address',
        'postal_address',
        'website',
        'region',
        'logo_path',
        'letterhead_path',
        'login_image_path',
        'use_custom_login_image',
        'type',
        'boarding',
        'school_sms_signature',
        'school_email_signature',
        'ownership',
    ];

    protected static function boot(){
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->school_id)) {
                $model->school_id = self::generateUniqueSchoolId($model->school_name, $model->type);
            }
        });

        static::updating(function ($model) {
            $prefix = self::getSchoolPrefix($model->school_name);
            $type = strtoupper($model->type);
            $expectedPrefix = $prefix . '-' . $type . '-';
            
            if (empty($model->school_id) || !str_starts_with($model->school_id, $expectedPrefix)) {
                $model->school_id = self::generateUniqueSchoolId($model->school_name, $model->type);
            }
        });
    }

    private static function getSchoolPrefix($schoolName){
        $cleanName = preg_replace('/[^A-Za-z0-9]/', '', $schoolName);
        $prefix = strtoupper(substr($cleanName, 0, 3));
        
        if (strlen($prefix) < 3) {
            $prefix = str_pad($prefix, 3, 'X');
        }
        
        return $prefix;
    }

    private static function generateUniqueSchoolId($schoolName, $type){
        $prefix = self::getSchoolPrefix($schoolName);
        $type = strtoupper($type);
        
        do {
            $schoolId = $prefix . '-' . $type . '-' . strtoupper(Str::random(8));
        } while (self::where('school_id', $schoolId)->exists());
        
        return $schoolId;
    }

    public static function schoolLogo(){
        $setup = self::latest()->first();
        return $setup ? $setup->logo_path : null;
    }

    public static function schoolLetterhead(){
        $setup = self::latest()->first();
        return $setup ? $setup->letterhead_path : null;
    }

    public static function current(): ?self
    {
        return self::query()->latest('id')->first();
    }

    public static function schoolType(): ?string
    {
        return self::normalizeType(self::current()?->type);
    }

    public static function isSeniorSchool(): bool
    {
        return in_array(self::schoolType(), [self::TYPE_SENIOR, self::TYPE_JUNIOR_SENIOR, self::TYPE_K12], true);
    }

    /**
     * @return array<int, string>
     */
    public static function validTypes(): array
    {
        return [
            self::TYPE_PRIMARY,
            self::TYPE_JUNIOR,
            self::TYPE_SENIOR,
            self::TYPE_PRE_F3,
            self::TYPE_JUNIOR_SENIOR,
            self::TYPE_K12,
        ];
    }

    public static function normalizeType(?string $type): ?string
    {
        if ($type === null) {
            return null;
        }

        $normalized = trim($type);

        return match (strtoupper($normalized)) {
            'PRIMARY' => self::TYPE_PRIMARY,
            'JUNIOR' => self::TYPE_JUNIOR,
            'SENIOR' => self::TYPE_SENIOR,
            'PRE_F3', 'PRE-F3' => self::TYPE_PRE_F3,
            'JUNIOR_SENIOR', 'JUNIOR-SENIOR' => self::TYPE_JUNIOR_SENIOR,
            'K12' => self::TYPE_K12,
            'UNIFIED' => self::inferLegacyUnifiedType(),
            default => $normalized,
        };
    }

    private static function inferLegacyUnifiedType(): string
    {
        try {
            if (Schema::hasTable('grades') && Grade::query()->where('level', self::LEVEL_SENIOR)->exists()) {
                return self::TYPE_K12;
            }

            if (Schema::hasTable('subjects') && Subject::query()->where('level', self::LEVEL_SENIOR)->exists()) {
                return self::TYPE_K12;
            }
        } catch (\Throwable $throwable) {
            Log::warning('Unable to infer legacy unified school type.', ['message' => $throwable->getMessage()]);
        }

        return self::TYPE_PRE_F3;
    }

    public static function schoolLoginImage(): string
    {
        $setup = self::current();
        if ($setup && $setup->use_custom_login_image && $setup->login_image_path) {
            return $setup->login_image_path;
        }
        return 'assets/images/login-page-image.jpg';
    }

    protected $casts = [
        'use_custom_login_image' => 'boolean',
    ];
}
