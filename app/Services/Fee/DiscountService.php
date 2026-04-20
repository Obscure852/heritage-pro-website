<?php

namespace App\Services\Fee;

use App\Models\Fee\DiscountType;
use App\Models\Fee\FeeAuditLog;
use App\Models\Fee\FeeStructure;
use App\Models\Fee\FeeType;
use App\Models\Fee\StudentDiscount;
use App\Models\Fee\StudentInvoice;
use App\Models\Fee\StudentInvoiceItem;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Service for managing discount types and student discount assignments.
 *
 * Discounts are assigned per student per year.
 */
class DiscountService
{
    /**
     * Create a new discount type.
     */
    public function createDiscountType(array $data): DiscountType
    {
        return DB::transaction(function () use ($data) {
            $discountType = DiscountType::create($data);

            FeeAuditLog::log(
                $discountType,
                FeeAuditLog::ACTION_CREATE,
                null,
                $discountType->toArray(),
                'Discount type created'
            );

            return $discountType;
        });
    }

    /**
     * Update an existing discount type.
     */
    public function updateDiscountType(DiscountType $type, array $data): DiscountType
    {
        return DB::transaction(function () use ($type, $data) {
            $oldValues = $type->toArray();

            $type->update($data);
            $type->refresh();

            FeeAuditLog::log(
                $type,
                FeeAuditLog::ACTION_UPDATE,
                $oldValues,
                $type->toArray(),
                'Discount type updated'
            );

            return $type;
        });
    }

    /**
     * Delete a discount type (soft delete).
     */
    public function deleteDiscountType(DiscountType $type): bool
    {
        return DB::transaction(function () use ($type) {
            $oldValues = $type->toArray();

            FeeAuditLog::log(
                $type,
                FeeAuditLog::ACTION_DELETE,
                $oldValues,
                null,
                'Discount type deleted'
            );

            return $type->delete();
        });
    }

    /**
     * Assign a discount to a student for a specific year.
     */
    public function assignDiscountToStudent(array $data, User $user): StudentDiscount
    {
        return DB::transaction(function () use ($data, $user) {
            $data['assigned_by'] = $user->id;

            $studentDiscount = StudentDiscount::create($data);

            FeeAuditLog::log(
                $studentDiscount,
                FeeAuditLog::ACTION_CREATE,
                null,
                $studentDiscount->toArray(),
                'Student discount assigned'
            );

            return $studentDiscount;
        });
    }

    /**
     * Remove a discount assignment from a student.
     */
    public function removeStudentDiscount(StudentDiscount $studentDiscount): bool
    {
        return DB::transaction(function () use ($studentDiscount) {
            $oldValues = $studentDiscount->toArray();

            FeeAuditLog::log(
                $studentDiscount,
                FeeAuditLog::ACTION_DELETE,
                $oldValues,
                null,
                'Student discount removed'
            );

            return $studentDiscount->delete();
        });
    }

    /**
     * Find students who share a sponsor with other students (potential siblings).
     * Returns students who have a sponsor with 2+ students enrolled for the given year.
     */
    public function findSiblingCandidates(int $year): Collection
    {
        // Find sponsor_ids with more than one current student enrolled for the given year
        $sponsorIds = Student::query()
            ->whereNotNull('sponsor_id')
            ->where('status', 'Current')
            ->whereHas('currentGrade')
            ->whereHas('terms', fn($q) => $q->where('terms.year', $year))
            ->select('sponsor_id')
            ->groupBy('sponsor_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('sponsor_id');

        // Get students with those sponsors who don't already have a sibling discount for the year
        return Student::query()
            ->whereIn('sponsor_id', $sponsorIds)
            ->where('status', 'Current')
            ->whereHas('currentGrade')
            ->whereHas('terms', fn($q) => $q->where('terms.year', $year))
            ->whereDoesntHave('feeDiscounts', function ($q) use ($year) {
                $q->where('year', $year)
                    ->whereHas('discountType', fn($dt) => $dt->where('code', 'LIKE', '%SIBLING%'));
            })
            ->with(['sponsor', 'currentGrade'])
            ->orderBy('sponsor_id')
            ->orderBy('last_name')
            ->get();
    }

    /**
     * Get siblings for a specific student (students sharing same sponsor).
     */
    public function getSiblingsForStudent(Student $student, int $year): Collection
    {
        if (!$student->sponsor_id) {
            return collect();
        }

        return Student::query()
            ->where('sponsor_id', $student->sponsor_id)
            ->where('id', '!=', $student->id)
            ->where('status', 'Current')
            ->whereHas('currentGrade')
            ->whereHas('terms', fn($q) => $q->where('terms.year', $year))
            ->with(['currentGrade'])
            ->get();
    }

    /**
     * Get all discounts assigned to a student for a specific year.
     */
    public function getStudentDiscountsForYear(int $studentId, int $year): Collection
    {
        return StudentDiscount::forStudent($studentId)
            ->forYear($year)
            ->with(['discountType', 'assignedBy'])
            ->get();
    }

    // ========================================
    // Discount Validation Methods
    // ========================================

    /**
     * Validate if a discount applies to a specific invoice item.
     *
     * Rules:
     * 1. Discounts NEVER apply to carryover items
     * 2. Discounts NEVER apply to credit note items
     * 3. Tuition-only discounts only apply to tuition category fees
     * 4. "All" discounts apply to all fee types (but not carryovers)
     *
     * @param DiscountType $discountType The discount to check
     * @param StudentInvoiceItem $item The invoice item
     * @return bool True if discount applies to this item
     */
    public function discountAppliesTo(DiscountType $discountType, StudentInvoiceItem $item): bool
    {
        // Rule 1: Discounts never apply to carryover items
        if ($item->item_type === StudentInvoiceItem::TYPE_CARRYOVER) {
            return false;
        }

        // Rule 2: Discounts never apply to credit note items
        if ($item->item_type === StudentInvoiceItem::TYPE_CREDIT_NOTE) {
            return false;
        }

        // Rule 3: Discounts never apply to adjustment items
        if ($item->item_type === StudentInvoiceItem::TYPE_ADJUSTMENT) {
            return false;
        }

        // Only fee items can receive discounts
        if ($item->item_type !== StudentInvoiceItem::TYPE_FEE) {
            return false;
        }

        // If no fee structure, we can't determine category
        if (!$item->fee_structure_id || !$item->feeStructure) {
            return false;
        }

        $feeCategory = $item->feeStructure->feeType->category ?? null;

        // Rule 4: Check discount scope
        if ($discountType->applies_to === DiscountType::APPLIES_TO_ALL) {
            return true;
        }

        if ($discountType->applies_to === DiscountType::APPLIES_TO_TUITION_ONLY) {
            return $feeCategory === FeeType::CATEGORY_TUITION;
        }

        return false;
    }

    /**
     * Calculate the total discount amount for an invoice item.
     *
     * @param StudentInvoiceItem $item The invoice item
     * @param Collection $discounts Collection of StudentDiscount models (with discountType loaded)
     * @return string The total discount amount as decimal string
     */
    public function calculateItemDiscountAmount(StudentInvoiceItem $item, Collection $discounts): string
    {
        if ($discounts->isEmpty()) {
            return '0.00';
        }

        $amount = (string) $item->amount;
        $totalDiscount = '0.00';

        foreach ($discounts as $studentDiscount) {
            $discountType = $studentDiscount->discountType;

            if (!$discountType || !$this->discountAppliesTo($discountType, $item)) {
                continue;
            }

            // Calculate discount: amount * (percentage / 100)
            $percentage = (string) $discountType->percentage;
            $discountAmount = bcmul($amount, bcdiv($percentage, '100', 4), 2);
            $totalDiscount = bcadd($totalDiscount, $discountAmount, 2);
        }

        // Cap discount at item amount
        if (bccomp($totalDiscount, $amount, 2) > 0) {
            return $amount;
        }

        return $totalDiscount;
    }

    /**
     * Validate an invoice's discount calculations.
     *
     * Returns an array of validation issues found, or empty array if valid.
     *
     * @param StudentInvoice $invoice The invoice to validate
     * @return array Array of validation issues (empty if valid)
     */
    public function validateInvoiceDiscounts(StudentInvoice $invoice): array
    {
        $issues = [];

        $invoice->load(['items.feeStructure.feeType']);

        $studentDiscounts = $this->getStudentDiscountsForYear($invoice->student_id, $invoice->year);

        foreach ($invoice->items as $item) {
            // Check carryover items have no discount
            if ($item->item_type === StudentInvoiceItem::TYPE_CARRYOVER) {
                if (bccomp((string) $item->discount_amount, '0.00', 2) !== 0) {
                    $issues[] = [
                        'type' => 'carryover_has_discount',
                        'item_id' => $item->id,
                        'item_type' => $item->item_type,
                        'description' => $item->description,
                        'discount_amount' => (string) $item->discount_amount,
                        'message' => "Carryover item '{$item->description}' should not have discount applied",
                    ];
                }
            }

            // Check credit note items have no discount
            if ($item->item_type === StudentInvoiceItem::TYPE_CREDIT_NOTE) {
                if (bccomp((string) $item->discount_amount, '0.00', 2) !== 0) {
                    $issues[] = [
                        'type' => 'credit_note_has_discount',
                        'item_id' => $item->id,
                        'item_type' => $item->item_type,
                        'description' => $item->description,
                        'discount_amount' => (string) $item->discount_amount,
                        'message' => "Credit note item '{$item->description}' should not have discount applied",
                    ];
                }
            }

            // For fee items, verify discount amount matches expected
            if ($item->item_type === StudentInvoiceItem::TYPE_FEE) {
                $expectedDiscount = $this->calculateItemDiscountAmount($item, $studentDiscounts);
                $actualDiscount = (string) $item->discount_amount;

                if (bccomp($expectedDiscount, $actualDiscount, 2) !== 0) {
                    $issues[] = [
                        'type' => 'discount_mismatch',
                        'item_id' => $item->id,
                        'item_type' => $item->item_type,
                        'description' => $item->description,
                        'expected_discount' => $expectedDiscount,
                        'actual_discount' => $actualDiscount,
                        'message' => "Fee item '{$item->description}' has incorrect discount: expected {$expectedDiscount}, actual {$actualDiscount}",
                    ];
                }
            }

            // Verify net_amount = amount - discount_amount
            $expectedNetAmount = bcsub((string) $item->amount, (string) $item->discount_amount, 2);
            $actualNetAmount = (string) $item->net_amount;

            if (bccomp($expectedNetAmount, $actualNetAmount, 2) !== 0) {
                $issues[] = [
                    'type' => 'net_amount_mismatch',
                    'item_id' => $item->id,
                    'description' => $item->description,
                    'expected_net' => $expectedNetAmount,
                    'actual_net' => $actualNetAmount,
                    'message' => "Item '{$item->description}' net_amount calculation error",
                ];
            }
        }

        return $issues;
    }

    /**
     * Get validation summary for all invoices for a year.
     *
     * Useful for batch validation of invoice discount calculations.
     *
     * @param int $year The year to validate
     * @return array{valid_count: int, invalid_count: int, issues: array}
     */
    public function validateYearInvoiceDiscounts(int $year): array
    {
        $invoices = StudentInvoice::forYear($year)
            ->active()
            ->get();

        $validCount = 0;
        $invalidCount = 0;
        $allIssues = [];

        foreach ($invoices as $invoice) {
            $issues = $this->validateInvoiceDiscounts($invoice);

            if (empty($issues)) {
                $validCount++;
            } else {
                $invalidCount++;
                $allIssues[$invoice->invoice_number] = [
                    'invoice_id' => $invoice->id,
                    'student_id' => $invoice->student_id,
                    'issues' => $issues,
                ];
            }
        }

        return [
            'valid_count' => $validCount,
            'invalid_count' => $invalidCount,
            'issues' => $allIssues,
        ];
    }
}
