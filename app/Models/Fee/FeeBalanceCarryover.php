<?php

namespace App\Models\Fee;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

/**
 * Fee balance carryovers track outstanding balances from one year to the next.
 *
 * If a student has unpaid fees from 2025, the balance is carried forward to 2026.
 * Unique constraint: (student_id, from_year, to_year)
 */
class FeeBalanceCarryover extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'from_year',
        'to_year',
        'balance_amount',
        'carried_at',
        'carried_by',
    ];

    protected $casts = [
        'from_year' => 'integer',
        'to_year' => 'integer',
        'balance_amount' => 'decimal:2',
        'carried_at' => 'datetime',
    ];

    /**
     * Get the student this carryover belongs to.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the user who performed the carryover.
     */
    public function carriedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'carried_by');
    }

    /**
     * Scope to filter by student.
     */
    public function scopeForStudent(Builder $query, int $studentId): Builder
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Scope to filter by destination year.
     */
    public function scopeToYear(Builder $query, int $year): Builder
    {
        return $query->where('to_year', $year);
    }

    /**
     * Scope to filter by source year.
     */
    public function scopeFromYear(Builder $query, int $year): Builder
    {
        return $query->where('from_year', $year);
    }

    /**
     * Scope to filter by year range (from_year to to_year).
     */
    public function scopeForYearRange(Builder $query, int $fromYear, int $toYear): Builder
    {
        return $query->where('from_year', $fromYear)->where('to_year', $toYear);
    }

    /**
     * Get total carryover balance for a student in a given year.
     */
    public static function getTotalCarryoverForStudent(int $studentId, int $year): float
    {
        return static::forStudent($studentId)
            ->toYear($year)
            ->sum('balance_amount');
    }

    /**
     * Check if a carryover already exists for this student and year range.
     */
    public static function existsForStudentYearRange(int $studentId, int $fromYear, int $toYear): bool
    {
        return static::forStudent($studentId)
            ->forYearRange($fromYear, $toYear)
            ->exists();
    }
}
