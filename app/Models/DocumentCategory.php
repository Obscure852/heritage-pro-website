<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model for hierarchical document categories.
 *
 * Supports parent-child relationships for nested category structures.
 * Each category can define retention periods and approval requirements.
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property int|null $parent_id
 * @property string|null $icon
 * @property string|null $color
 * @property int $sort_order
 * @property int|null $retention_days
 * @property bool $requires_approval
 * @property bool $is_active
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read DocumentCategory|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection|DocumentCategory[] $children
 * @property-read \Illuminate\Database\Eloquent\Collection|Document[] $documents
 */
class DocumentCategory extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
        'icon',
        'color',
        'sort_order',
        'retention_days',
        'requires_approval',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'requires_approval' => 'boolean',
        'is_active' => 'boolean',
        'retention_days' => 'integer',
        'sort_order' => 'integer',
    ];

    // ==================== RELATIONSHIPS ====================

    /**
     * Get the parent category.
     */
    public function parent(): BelongsTo {
        return $this->belongsTo(DocumentCategory::class, 'parent_id');
    }

    /**
     * Get the child categories.
     */
    public function children(): HasMany {
        return $this->hasMany(DocumentCategory::class, 'parent_id');
    }

    /**
     * Get the documents in this category.
     */
    public function documents(): HasMany {
        return $this->hasMany(Document::class, 'category_id');
    }
}
