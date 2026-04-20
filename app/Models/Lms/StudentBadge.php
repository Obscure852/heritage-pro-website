<?php

namespace App\Models\Lms;

use App\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentBadge extends Model {
    protected $table = 'lms_student_badges';

    protected $fillable = [
        'student_id',
        'badge_id',
        'course_id',
        'earned_at',
        'metadata',
        'is_featured',
    ];

    protected $casts = [
        'earned_at' => 'datetime',
        'metadata' => 'array',
        'is_featured' => 'boolean',
    ];

    public function student(): BelongsTo {
        return $this->belongsTo(Student::class);
    }

    public function badge(): BelongsTo {
        return $this->belongsTo(Badge::class);
    }

    public function course(): BelongsTo {
        return $this->belongsTo(Course::class);
    }

    public function scopeFeatured($query) {
        return $query->where('is_featured', true);
    }

    public function scopeRecent($query, int $days = 30) {
        return $query->where('earned_at', '>=', now()->subDays($days));
    }

    public function toggleFeatured(): bool {
        $this->is_featured = !$this->is_featured;
        return $this->save();
    }
}
