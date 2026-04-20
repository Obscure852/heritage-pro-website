<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Model for document tags (searchable labels).
 *
 * Supports official (admin-only) and user-created tags.
 * Includes denormalized usage_count for efficient tag cloud/sorting.
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string|null $color
 * @property bool $is_official
 * @property int $usage_count
 * @property int|null $created_by_user_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|Document[] $documents
 * @property-read User|null $createdBy
 */
class DocumentTag extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'is_official',
        'usage_count',
        'created_by_user_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_official' => 'boolean',
        'usage_count' => 'integer',
    ];

    // ==================== RELATIONSHIPS ====================

    /**
     * Get the documents that have this tag.
     */
    public function documents(): BelongsToMany {
        return $this->belongsToMany(Document::class, 'document_tag', 'tag_id', 'document_id')
            ->withPivot('tagged_by_user_id')
            ->withTimestamps();
    }

    /**
     * Get the user who created this tag.
     */
    public function createdBy(): BelongsTo {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
