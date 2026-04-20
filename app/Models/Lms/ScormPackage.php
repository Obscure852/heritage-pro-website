<?php

namespace App\Models\Lms;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class ScormPackage extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'lms_scorm_packages';

    protected $fillable = [
        'title',
        'description',
        'version',
        'zip_path',
        'extracted_path',
        'launch_url',
        'identifier',
        'manifest_data',
        'organizations',
        'resources',
        'mastery_score',
        'time_limit_minutes',
        'allow_review',
        'max_attempts',
        'package_size',
        'uploaded_by',
    ];

    protected $casts = [
        'manifest_data' => 'array',
        'organizations' => 'array',
        'resources' => 'array',
        'allow_review' => 'boolean',
    ];

    public const VERSIONS = [
        '1.2' => 'SCORM 1.2',
        '2004_2nd' => 'SCORM 2004 2nd Edition',
        '2004_3rd' => 'SCORM 2004 3rd Edition',
        '2004_4th' => 'SCORM 2004 4th Edition',
    ];

    // Relationships
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(ScormAttempt::class, 'package_id');
    }

    public function contentItem(): MorphOne
    {
        return $this->morphOne(ContentItem::class, 'contentable');
    }

    // Accessors
    public function getIsScorm12Attribute(): bool
    {
        return $this->version === '1.2';
    }

    public function getIsScorm2004Attribute(): bool
    {
        return str_starts_with($this->version, '2004');
    }

    public function getLaunchUrlFullAttribute(): string
    {
        return Storage::disk('public')->url($this->extracted_path . '/' . $this->launch_url);
    }

    public function getPackageSizeFormattedAttribute(): string
    {
        $bytes = $this->package_size;

        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }

        return $bytes . ' bytes';
    }

    // Methods
    public function getAttemptForStudent(int $studentId): ?ScormAttempt
    {
        return $this->attempts()
            ->where('student_id', $studentId)
            ->latest('attempt_number')
            ->first();
    }

    public function canStudentAttempt(int $studentId): bool
    {
        if (!$this->max_attempts) {
            return true;
        }

        $attemptCount = $this->attempts()
            ->where('student_id', $studentId)
            ->count();

        return $attemptCount < $this->max_attempts;
    }

    public function getOrCreateAttempt(int $studentId, ?int $contentItemId = null): ScormAttempt
    {
        // Check for existing incomplete attempt
        $existingAttempt = $this->attempts()
            ->where('student_id', $studentId)
            ->whereNull('completed_at')
            ->first();

        if ($existingAttempt) {
            return $existingAttempt;
        }

        // Create new attempt
        $attemptNumber = $this->attempts()
            ->where('student_id', $studentId)
            ->count() + 1;

        return ScormAttempt::create([
            'package_id' => $this->id,
            'content_item_id' => $contentItemId,
            'student_id' => $studentId,
            'attempt_number' => $attemptNumber,
            'started_at' => now(),
            'entry' => $attemptNumber === 1 ? 'ab-initio' : '',
        ]);
    }

    public function deletePackageFiles(): void
    {
        if ($this->zip_path) {
            Storage::disk('public')->delete($this->zip_path);
        }

        if ($this->extracted_path) {
            Storage::disk('public')->deleteDirectory($this->extracted_path);
        }
    }
}
