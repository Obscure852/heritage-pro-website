<?php

namespace App\Models\Fee;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

/**
 * Student clearances track fee clearance status per year.
 *
 * A student is "cleared" for a year if their annual balance is zero
 * or if an override has been granted by an authorized user.
 * Unique constraint: (student_id, year)
 */
class StudentClearance extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'year',
        'override_granted',
        'granted_by',
        'granted_at',
        'reason',
        'notes',
    ];

    protected $casts = [
        'year' => 'integer',
        'override_granted' => 'boolean',
        'granted_at' => 'datetime',
    ];

    /**
     * Get the student this clearance belongs to.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the user who granted the override.
     */
    public function grantedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_by');
    }

    /**
     * Scope to filter by student.
     */
    public function scopeForStudent(Builder $query, int $studentId): Builder
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Scope to filter by year.
     */
    public function scopeForYear(Builder $query, int $year): Builder
    {
        return $query->where('year', $year);
    }

    /**
     * Scope to get records with override granted.
     */
    public function scopeOverrideGranted(Builder $query): Builder
    {
        return $query->where('override_granted', true);
    }

    /**
     * Check if this record has an override granted.
     */
    public function hasOverride(): bool
    {
        return $this->override_granted;
    }

    /**
     * Grant a clearance override for this student/year.
     */
    public function grantOverride(User $grantedBy, string $reason, ?string $notes = null): bool
    {
        $this->override_granted = true;
        $this->granted_by = $grantedBy->id;
        $this->granted_at = now();
        $this->reason = $reason;
        $this->notes = $notes;

        return $this->save();
    }

    /**
     * Revoke a previously granted override.
     */
    public function revokeOverride(): bool
    {
        $this->override_granted = false;
        $this->granted_by = null;
        $this->granted_at = null;
        $this->reason = null;

        return $this->save();
    }
}
