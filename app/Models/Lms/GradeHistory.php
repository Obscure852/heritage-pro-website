<?php

namespace App\Models\Lms;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradeHistory extends Model {
    protected $table = 'lms_grade_history';

    protected $fillable = [
        'grade_id',
        'old_score',
        'new_score',
        'old_status',
        'new_status',
        'action',
        'reason',
        'changed_by',
        'changed_at',
    ];

    protected $casts = [
        'old_score' => 'decimal:2',
        'new_score' => 'decimal:2',
        'changed_at' => 'datetime',
    ];

    public const ACTION_CREATED = 'created';
    public const ACTION_UPDATED = 'updated';
    public const ACTION_EXCUSED = 'excused';
    public const ACTION_DROPPED = 'dropped';
    public const ACTION_OVERRIDDEN = 'overridden';

    // Relationships
    public function grade(): BelongsTo {
        return $this->belongsTo(Grade::class, 'grade_id');
    }

    public function changedBy(): BelongsTo {
        return $this->belongsTo(User::class, 'changed_by');
    }

    // Accessors
    public function getActionLabelAttribute(): string {
        return match ($this->action) {
            self::ACTION_CREATED => 'Grade Created',
            self::ACTION_UPDATED => 'Grade Updated',
            self::ACTION_EXCUSED => 'Marked Excused',
            self::ACTION_DROPPED => 'Grade Dropped',
            self::ACTION_OVERRIDDEN => 'Grade Overridden',
            default => ucfirst($this->action),
        };
    }

    public function getChangeDescriptionAttribute(): string {
        if ($this->action === self::ACTION_CREATED) {
            return "Initial grade: {$this->new_score}";
        }

        if ($this->old_score !== null && $this->new_score !== null) {
            return "Changed from {$this->old_score} to {$this->new_score}";
        }

        if ($this->old_status !== $this->new_status) {
            return "Status changed from {$this->old_status} to {$this->new_status}";
        }

        return $this->action_label;
    }
}
