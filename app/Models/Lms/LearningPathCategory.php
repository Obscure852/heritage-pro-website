<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class LearningPathCategory extends Model {
    protected $table = 'lms_learning_path_categories';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'color',
        'position',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function boot(): void {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    // Relationships
    public function learningPaths(): BelongsToMany {
        return $this->belongsToMany(LearningPath::class, 'lms_learning_path_category', 'category_id', 'learning_path_id');
    }

    // Scopes
    public function scopeActive($query) {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query) {
        return $query->orderBy('position');
    }

    // Accessors
    public function getPathsCountAttribute(): int {
        return $this->learningPaths()->published()->count();
    }
}
