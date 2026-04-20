<?php

namespace App\Models\Fee;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

/**
 * Student discounts track which discount types are assigned to students per year.
 *
 * A discount type (e.g., "Staff Child", "Sibling") can be assigned once per student per year.
 * Unique constraint: (student_id, discount_type_id, year)
 */
class StudentDiscount extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'discount_type_id',
        'year',
        'assigned_by',
        'notes',
    ];

    protected $casts = [
        'year' => 'integer',
    ];

    /**
     * Get the student this discount is assigned to.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the discount type.
     */
    public function discountType(): BelongsTo
    {
        return $this->belongsTo(DiscountType::class);
    }

    /**
     * Get the user who assigned this discount.
     */
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Scope to filter by year.
     */
    public function scopeForYear(Builder $query, int $year): Builder
    {
        return $query->where('year', $year);
    }

    /**
     * Scope to filter by student.
     */
    public function scopeForStudent(Builder $query, int $studentId): Builder
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Scope to filter by discount type.
     */
    public function scopeForDiscountType(Builder $query, int $discountTypeId): Builder
    {
        return $query->where('discount_type_id', $discountTypeId);
    }

    /**
     * Scope to get only discounts with active discount types.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereHas('discountType', function ($q) {
            $q->where('is_active', true);
        });
    }

    /**
     * Calculate the discount amount based on the discount type percentage and fee subtotal.
     */
    public function calculateAmount(float $subtotal): float
    {
        if (!$this->discountType) {
            return 0;
        }

        // All discount types use percentage (e.g., 10% = 10.00)
        $percentage = (float) $this->discountType->percentage;
        return round(($subtotal * $percentage) / 100, 2);
    }
}
