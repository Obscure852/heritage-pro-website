<?php

namespace App\Models\Timetable;

use App\Models\Term;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Timetable model.
 *
 * Represents a timetable for a specific term with draft/published/archived workflow.
 *
 * @property int $id
 * @property int $term_id
 * @property string $name
 * @property string $status
 * @property \Carbon\Carbon|null $published_at
 * @property int|null $published_by
 * @property int $created_by
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class Timetable extends Model {
    use HasFactory, SoftDeletes;

    // ==================== CONSTANTS ====================

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_ARCHIVED = 'archived';

    // ==================== ATTRIBUTES ====================

    protected $fillable = [
        'name',
        'term_id',
        'status',
        'published_at',
        'published_by',
        'created_by',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // ==================== RELATIONSHIPS ====================

    public function term(): BelongsTo {
        return $this->belongsTo(Term::class);
    }

    public function creator(): BelongsTo {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function publisher(): BelongsTo {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function slots(): HasMany {
        return $this->hasMany(TimetableSlot::class);
    }

    public function constraints(): HasMany {
        return $this->hasMany(TimetableConstraint::class);
    }

    public function conflicts(): HasMany {
        return $this->hasMany(TimetableConflict::class);
    }

    public function auditLogs(): HasMany {
        return $this->hasMany(TimetableAuditLog::class);
    }

    public function blockAllocations(): HasMany {
        return $this->hasMany(TimetableBlockAllocation::class);
    }

    public function versions(): HasMany {
        return $this->hasMany(TimetableVersion::class);
    }

    // ==================== SCOPES ====================

    public function scopeDraft($query) {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopePublished($query) {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function scopeForTerm($query, int $termId) {
        return $query->where('term_id', $termId);
    }

    public function scopeArchived($query) {
        return $query->where('status', self::STATUS_ARCHIVED);
    }

    // ==================== HELPERS ====================

    public function isDraft(): bool {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isPublished(): bool {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function isArchived(): bool {
        return $this->status === self::STATUS_ARCHIVED;
    }
}
