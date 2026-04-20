<?php

namespace App\Models\Fee;

use App\Models\Grade;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

/**
 * Fee structures define the fee amounts per fee type, grade, and year.
 *
 * Each structure represents a single fee line item (e.g., "Tuition for Grade 8 in 2026").
 * Unique constraint: (fee_type_id, grade_id, year)
 */
class FeeStructure extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'fee_type_id',
        'grade_id',
        'year',
        'amount',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'year' => 'integer',
    ];

    /**
     * Get the fee type for this structure.
     */
    public function feeType(): BelongsTo
    {
        return $this->belongsTo(FeeType::class);
    }

    /**
     * Get the grade for this structure.
     */
    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    /**
     * Get the user who created this structure.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the invoice items using this fee structure.
     */
    public function invoiceItems(): HasMany
    {
        return $this->hasMany(StudentInvoiceItem::class);
    }

    /**
     * Scope to filter by grade.
     */
    public function scopeForGrade(Builder $query, int $gradeId): Builder
    {
        return $query->where('grade_id', $gradeId);
    }

    /**
     * Scope to filter by year.
     */
    public function scopeForYear(Builder $query, int $year): Builder
    {
        return $query->where('year', $year);
    }

    /**
     * Scope to filter by fee type.
     */
    public function scopeForFeeType(Builder $query, int $feeTypeId): Builder
    {
        return $query->where('fee_type_id', $feeTypeId);
    }

    /**
     * Scope to get only active fee structures (with active fee types).
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereHas('feeType', function ($q) {
            $q->where('is_active', true);
        });
    }

    /**
     * Get the total fees for a grade in a given year.
     */
    public static function getTotalForGradeYear(int $gradeId, int $year): float
    {
        return static::forGrade($gradeId)
            ->forYear($year)
            ->active()
            ->sum('amount');
    }
}
