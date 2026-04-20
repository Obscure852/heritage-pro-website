<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Module extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'lms_modules';

    protected $fillable = [
        'course_id',
        'title',
        'description',
        'sequence',
        'unlock_date',
        'prerequisites',
        'is_locked',
        'prerequisite_module_id',
        'require_sequential_completion',
    ];

    protected $casts = [
        'unlock_date' => 'datetime',
        'prerequisites' => 'array',
        'is_locked' => 'boolean',
        'require_sequential_completion' => 'boolean',
    ];

    // Relationships
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function contentItems(): HasMany
    {
        return $this->hasMany(ContentItem::class)->orderBy('sequence');
    }

    public function prerequisiteModule(): BelongsTo
    {
        return $this->belongsTo(Module::class, 'prerequisite_module_id');
    }

    public function dependentModules(): HasMany
    {
        return $this->hasMany(Module::class, 'prerequisite_module_id');
    }

    // Scopes
    public function scopeOrdered($query)
    {
        return $query->orderBy('sequence');
    }

    public function scopeUnlocked($query)
    {
        return $query->where('is_locked', false)
            ->where(function ($q) {
                $q->whereNull('unlock_date')
                    ->orWhere('unlock_date', '<=', now());
            });
    }

    // Accessors
    public function getContentCountAttribute(): int
    {
        return $this->contentItems()->count();
    }

    public function getTotalDurationAttribute(): int
    {
        return $this->contentItems()->sum('duration_minutes') ?? 0;
    }

    // Helper Methods
    public function isUnlocked(): bool
    {
        if ($this->is_locked) {
            return false;
        }

        if ($this->unlock_date && now()->lt($this->unlock_date)) {
            return false;
        }

        return true;
    }

    public function hasPrerequisites(): bool
    {
        return !empty($this->prerequisites);
    }

    public function prerequisitesMet(Enrollment $enrollment): bool
    {
        if (!$this->hasPrerequisites()) {
            return true;
        }

        foreach ($this->prerequisites as $prerequisiteId) {
            $prerequisiteModule = Module::find($prerequisiteId);
            if (!$prerequisiteModule) {
                continue;
            }

            $allContentComplete = $prerequisiteModule->contentItems()
                ->where('is_mandatory', true)
                ->get()
                ->every(function ($content) use ($enrollment) {
                    $progress = ContentProgress::where('enrollment_id', $enrollment->id)
                        ->where('content_item_id', $content->id)
                        ->first();
                    return $progress && $progress->status === 'completed';
                });

            if (!$allContentComplete) {
                return false;
            }
        }

        return true;
    }
}
