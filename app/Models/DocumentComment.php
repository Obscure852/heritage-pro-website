<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model for threaded comments and annotations on documents.
 *
 * Supports parent-child threading for replies and position fields for PDF annotations.
 * Comments can be resolved as part of the review workflow.
 *
 * @property int $id
 * @property int $document_id
 * @property int|null $version_id
 * @property int $user_id
 * @property int|null $parent_id
 * @property string $content
 * @property int|null $page_number
 * @property float|null $position_x
 * @property float|null $position_y
 * @property bool $is_resolved
 * @property int|null $resolved_by_user_id
 * @property \Carbon\Carbon|null $resolved_at
 * @property bool $is_edited
 * @property \Carbon\Carbon|null $edited_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read Document $document
 * @property-read DocumentVersion|null $version
 * @property-read User $user
 * @property-read DocumentComment|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection|DocumentComment[] $replies
 * @property-read User|null $resolvedBy
 */
class DocumentComment extends Model {
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'document_id',
        'version_id',
        'user_id',
        'parent_id',
        'content',
        'page_number',
        'position_x',
        'position_y',
        'is_resolved',
        'resolved_by_user_id',
        'resolved_at',
        'is_edited',
        'edited_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_resolved' => 'boolean',
        'is_edited' => 'boolean',
        'resolved_at' => 'datetime',
        'edited_at' => 'datetime',
        'page_number' => 'integer',
        'position_x' => 'float',
        'position_y' => 'float',
    ];

    // ==================== RELATIONSHIPS ====================

    /**
     * Get the document this comment belongs to.
     */
    public function document(): BelongsTo {
        return $this->belongsTo(Document::class);
    }

    /**
     * Get the document version this comment is on.
     */
    public function version(): BelongsTo {
        return $this->belongsTo(DocumentVersion::class, 'version_id');
    }

    /**
     * Get the user who wrote this comment.
     */
    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent comment (for threaded replies).
     */
    public function parent(): BelongsTo {
        return $this->belongsTo(DocumentComment::class, 'parent_id');
    }

    /**
     * Get the replies to this comment.
     */
    public function replies(): HasMany {
        return $this->hasMany(DocumentComment::class, 'parent_id');
    }

    /**
     * Get the user who resolved this comment.
     */
    public function resolvedBy(): BelongsTo {
        return $this->belongsTo(User::class, 'resolved_by_user_id');
    }
}
