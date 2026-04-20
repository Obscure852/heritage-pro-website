<?php

namespace App\Models\Lms;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseTemplate extends Model {
    protected $table = 'lms_course_templates';

    protected $fillable = [
        'name',
        'description',
        'thumbnail_path',
        'category',
        'structure',
        'settings',
        'is_public',
        'usage_count',
        'created_by',
    ];

    protected $casts = [
        'structure' => 'array',
        'settings' => 'array',
        'is_public' => 'boolean',
    ];

    public static array $categories = [
        'academic' => 'Academic Course',
        'training' => 'Training Program',
        'workshop' => 'Workshop',
        'orientation' => 'Orientation',
        'certification' => 'Certification',
        'blank' => 'Blank Template',
    ];

    public function creator(): BelongsTo {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopePublic($query) {
        return $query->where('is_public', true);
    }

    public function incrementUsage(): void {
        $this->increment('usage_count');
    }

    public function getModuleCountAttribute(): int {
        return count($this->structure['modules'] ?? []);
    }
}
