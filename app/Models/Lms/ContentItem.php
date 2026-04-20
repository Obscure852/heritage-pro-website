<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class ContentItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'lms_content_items';

    protected $fillable = [
        'module_id',
        'title',
        'description',
        'content_type',
        'content_data',
        'sequence',
        'duration_minutes',
        'is_mandatory',
        'passing_score',
        'max_attempts',
        'unlock_conditions',
        'available_from',
        'available_until',
        'contentable_id',
        'contentable_type',
        'library_item_id',
        'type',
        'content',
        'file_path',
        'external_url',
        'is_required',
        'estimated_duration',
    ];

    protected $casts = [
        'content_data' => 'array',
        'unlock_conditions' => 'array',
        'is_mandatory' => 'boolean',
        'passing_score' => 'decimal:2',
        'available_from' => 'datetime',
        'available_until' => 'datetime',
    ];

    public const CONTENT_TYPES = [
        'text' => 'Text/HTML',
        'document' => 'Document',
        'video_youtube' => 'YouTube Video',
        'video_upload' => 'Uploaded Video',
        'audio' => 'Audio',
        'image' => 'Image',
        'scorm12' => 'SCORM 1.2',
        'scorm2004' => 'SCORM 2004',
        'h5p' => 'H5P Interactive',
        'quiz' => 'Quiz',
        'assignment' => 'Assignment',
        'live_session' => 'Live Session',
        'external_url' => 'External URL',
        'lti_tool' => 'LTI Tool',
    ];

    // Relationships
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function course(): BelongsTo
    {
        return $this->module->course();
    }

    public function progress(): HasMany
    {
        return $this->hasMany(ContentProgress::class);
    }

    public function video(): HasOne
    {
        return $this->hasOne(Video::class);
    }

    public function document(): HasOne
    {
        return $this->hasOne(Document::class);
    }

    public function quiz(): HasOne
    {
        return $this->hasOne(Quiz::class);
    }

    /**
     * Get the contentable model (Video, Document, Quiz, etc.)
     * This is the polymorphic relationship used for different content types.
     */
    public function contentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the library item if this content was created from the library.
     */
    public function libraryItem(): BelongsTo
    {
        return $this->belongsTo(LibraryItem::class, 'library_item_id');
    }

    /**
     * Check if this content item was created from the content library.
     */
    public function isFromLibrary(): bool
    {
        return $this->library_item_id !== null;
    }

    /**
     * Get the file URL, working for both library-sourced and uploaded content.
     */
    public function getFileUrlAttribute(): ?string
    {
        // If from library, use the library item's file path
        if ($this->isFromLibrary() && $this->libraryItem) {
            if ($this->libraryItem->file_path) {
                return Storage::disk('public')->url($this->libraryItem->file_path);
            }
            return $this->libraryItem->external_url;
        }

        // Otherwise use the content item's own file path
        if ($this->file_path) {
            return Storage::disk('public')->url($this->file_path);
        }

        // Check contentable for file path
        if ($this->contentable && property_exists($this->contentable, 'file_path') && $this->contentable->file_path) {
            return Storage::disk('public')->url($this->contentable->file_path);
        }

        return $this->external_url;
    }

    // Scopes
    public function scopeOrdered($query)
    {
        return $query->orderBy('sequence');
    }

    public function scopeMandatory($query)
    {
        return $query->where('is_mandatory', true);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('content_type', $type);
    }

    public function scopeAvailable($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('available_from')
                ->orWhere('available_from', '<=', now());
        })->where(function ($q) {
            $q->whereNull('available_until')
                ->orWhere('available_until', '>=', now());
        });
    }

    // Accessors
    public function getContentTypeNameAttribute(): string
    {
        return self::CONTENT_TYPES[$this->content_type] ?? $this->content_type;
    }

    public function getIsScorableAttribute(): bool
    {
        return in_array($this->content_type, [
            'quiz', 'assignment', 'scorm12', 'scorm2004', 'h5p', 'lti_tool'
        ]);
    }

    public function getIsTrackableAttribute(): bool
    {
        return in_array($this->content_type, [
            'video_youtube', 'video_upload', 'audio', 'scorm12', 'scorm2004'
        ]);
    }

    // Helper Methods
    public function isAvailable(): bool
    {
        $now = now();

        if ($this->available_from && $now->lt($this->available_from)) {
            return false;
        }

        if ($this->available_until && $now->gt($this->available_until)) {
            return false;
        }

        return true;
    }

    public function isVideo(): bool
    {
        return in_array($this->content_type, ['video_youtube', 'video_upload']);
    }

    public function isDocument(): bool
    {
        return $this->content_type === 'document';
    }

    public function isQuiz(): bool
    {
        return $this->content_type === 'quiz';
    }

    public function isScorm(): bool
    {
        return in_array($this->content_type, ['scorm12', 'scorm2004']);
    }

    public function getProgressForStudent($studentId): ?ContentProgress
    {
        $enrollment = Enrollment::where('course_id', $this->module->course_id)
            ->where('student_id', $studentId)
            ->first();

        if (!$enrollment) {
            return null;
        }

        return $this->progress()
            ->where('enrollment_id', $enrollment->id)
            ->first();
    }
}
