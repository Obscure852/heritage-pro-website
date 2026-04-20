<?php

namespace App\Models\Welfare;

use App\Models\User;
use App\Traits\Welfare\Auditable;
use App\Traits\Welfare\Encryptable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Welfare case note model.
 *
 * General notes attached to welfare cases.
 * Supports confidential notes with encryption.
 *
 * @property int $id
 * @property int $welfare_case_id
 * @property int $created_by
 * @property string $note_type
 * @property string $content
 * @property bool $is_confidential
 * @property bool $is_pinned
 */
class WelfareCaseNote extends Model
{
    use HasFactory, SoftDeletes, Auditable, Encryptable;

    protected $fillable = [
        'welfare_case_id',
        'created_by',
        'note_type',
        'content',
        'is_confidential',
        'is_pinned',
    ];

    protected $casts = [
        'is_confidential' => 'boolean',
        'is_pinned' => 'boolean',
    ];

    /**
     * Fields that should be encrypted when confidential.
     */
    protected array $encryptable = [
        'content',
    ];

    // Note type constants
    public const TYPE_GENERAL = 'general';
    public const TYPE_PROGRESS = 'progress';
    public const TYPE_OBSERVATION = 'observation';
    public const TYPE_MEETING = 'meeting';
    public const TYPE_FOLLOW_UP = 'follow_up';
    public const TYPE_INTERNAL = 'internal';

    // ==================== RELATIONSHIPS ====================

    public function welfareCase()
    {
        return $this->belongsTo(WelfareCase::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ==================== ENCRYPTION OVERRIDE ====================

    /**
     * Override to only encrypt content when note is confidential.
     */
    public function setAttribute($key, $value)
    {
        // Only encrypt content if the note is marked as confidential
        if ($key === 'content' && $this->is_confidential && !empty($value)) {
            $value = $this->encryptValue($value);
        } elseif ($key !== 'content' || !$this->is_confidential) {
            // For non-confidential notes, use parent directly
            if (in_array($key, $this->encryptable ?? [])) {
                return parent::setAttribute($key, $value);
            }
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * Override to only decrypt content when note is confidential.
     */
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        if ($key === 'content' && $this->is_confidential && !empty($value)) {
            return $this->decryptValue($value);
        }

        return $value;
    }

    // ==================== SCOPES ====================

    public function scopeConfidential(Builder $query): Builder
    {
        return $query->where('is_confidential', true);
    }

    public function scopeNonConfidential(Builder $query): Builder
    {
        return $query->where('is_confidential', false);
    }

    public function scopePinned(Builder $query): Builder
    {
        return $query->where('is_pinned', true);
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('note_type', $type);
    }

    public function scopeByAuthor(Builder $query, int $userId): Builder
    {
        return $query->where('created_by', $userId);
    }

    public function scopeRecent(Builder $query, int $days = 7): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // ==================== HELPER METHODS ====================

    public function isConfidential(): bool
    {
        return $this->is_confidential;
    }

    public function isPinned(): bool
    {
        return $this->is_pinned;
    }

    /**
     * Toggle pinned status.
     */
    public function togglePin(): bool
    {
        return $this->update(['is_pinned' => !$this->is_pinned]);
    }

    /**
     * Pin the note.
     */
    public function pin(): bool
    {
        return $this->update(['is_pinned' => true]);
    }

    /**
     * Unpin the note.
     */
    public function unpin(): bool
    {
        return $this->update(['is_pinned' => false]);
    }

    /**
     * Get note type badge color for UI.
     */
    public function getTypeColorAttribute(): string
    {
        return match ($this->note_type) {
            self::TYPE_GENERAL => 'gray',
            self::TYPE_PROGRESS => 'green',
            self::TYPE_OBSERVATION => 'blue',
            self::TYPE_MEETING => 'purple',
            self::TYPE_FOLLOW_UP => 'orange',
            self::TYPE_INTERNAL => 'red',
            default => 'gray',
        };
    }

    /**
     * Get truncated content for display.
     */
    public function getTruncatedContentAttribute(): string
    {
        $content = $this->content ?? '';

        if (strlen($content) <= 150) {
            return $content;
        }

        return substr($content, 0, 150) . '...';
    }
}
