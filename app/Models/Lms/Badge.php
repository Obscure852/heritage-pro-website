<?php

namespace App\Models\Lms;

use App\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Badge extends Model {
    protected $table = 'lms_badges';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'color',
        'category',
        'rarity',
        'points_value',
        'criteria',
        'is_active',
        'is_secret',
        'sort_order',
    ];

    protected $casts = [
        'criteria' => 'array',
        'is_active' => 'boolean',
        'is_secret' => 'boolean',
        'points_value' => 'integer',
    ];

    public const CATEGORY_COMPLETION = 'completion';
    public const CATEGORY_ACHIEVEMENT = 'achievement';
    public const CATEGORY_STREAK = 'streak';
    public const CATEGORY_SOCIAL = 'social';
    public const CATEGORY_SPECIAL = 'special';

    public const RARITY_COMMON = 'common';
    public const RARITY_UNCOMMON = 'uncommon';
    public const RARITY_RARE = 'rare';
    public const RARITY_EPIC = 'epic';
    public const RARITY_LEGENDARY = 'legendary';

    public static array $rarityColors = [
        'common' => '#9ca3af',
        'uncommon' => '#22c55e',
        'rare' => '#3b82f6',
        'epic' => '#a855f7',
        'legendary' => '#f59e0b',
    ];

    public function students(): BelongsToMany {
        return $this->belongsToMany(Student::class, 'lms_student_badges')
            ->withPivot(['course_id', 'earned_at', 'metadata', 'is_featured'])
            ->withTimestamps();
    }

    public function studentBadges(): HasMany {
        return $this->hasMany(StudentBadge::class);
    }

    public function achievements(): HasMany {
        return $this->hasMany(Achievement::class);
    }

    public function scopeActive($query) {
        return $query->where('is_active', true);
    }

    public function scopeVisible($query) {
        return $query->where('is_secret', false);
    }

    public function scopeByCategory($query, string $category) {
        return $query->where('category', $category);
    }

    public function getRarityColorAttribute(): string {
        return self::$rarityColors[$this->rarity] ?? self::$rarityColors['common'];
    }

    public function getIconClassAttribute(): string {
        return $this->icon ?: 'fas fa-award';
    }

    public function awardTo(Student $student, ?Course $course = null, array $metadata = []): StudentBadge {
        return StudentBadge::create([
            'student_id' => $student->id,
            'badge_id' => $this->id,
            'course_id' => $course?->id,
            'earned_at' => now(),
            'metadata' => $metadata,
        ]);
    }

    public function isEarnedBy(Student $student, ?Course $course = null): bool {
        $query = $this->studentBadges()
            ->where('student_id', $student->id);

        if ($course) {
            $query->where('course_id', $course->id);
        }

        return $query->exists();
    }
}
