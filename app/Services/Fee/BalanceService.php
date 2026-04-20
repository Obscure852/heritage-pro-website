<?php

namespace App\Services\Fee;

use App\Models\Fee\FeeAuditLog;
use App\Models\Fee\FeeBalanceCarryover;
use App\Models\Fee\StudentClearance;
use App\Models\Fee\StudentInvoice;
use App\Models\Student;
use App\Models\Term;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Service for managing student fee balances and clearances.
 *
 * Balances and clearances are tracked per student per year.
 */
class BalanceService
{
    /**
     * Get the maximum allowed carryover lookback years.
     * Based on how long the school has been using the system.
     */
    public static function getMaxLookbackYears(): int
    {
        $currentYear = (int) date('Y');
        $earliestYear = Term::min('year');

        if ($earliestYear === null) {
            return 1;
        }

        return max(1, $currentYear - (int) $earliestYear);
    }

    /**
     * Get student balance with detailed breakdown.
     *
     * @param int $studentId
     * @param int|null $year If provided, filter to that year only
     * @return array{total_invoiced: string, total_paid: string, balance: string, invoice_count: int}
     */
    public function getStudentBalance(int $studentId, ?int $year = null): array
    {
        $query = StudentInvoice::forStudent($studentId)->active();

        if ($year !== null) {
            $query->forYear($year);
        }

        $invoices = $query->get();

        $totalInvoiced = '0.00';
        $totalPaid = '0.00';

        foreach ($invoices as $invoice) {
            $totalInvoiced = bcadd($totalInvoiced, (string) $invoice->total_amount, 2);
            $totalPaid = bcadd($totalPaid, (string) $invoice->amount_paid, 2);
        }

        $balance = bcsub($totalInvoiced, $totalPaid, 2);

        return [
            'total_invoiced' => $totalInvoiced,
            'total_paid' => $totalPaid,
            'balance' => $balance,
            'invoice_count' => $invoices->count(),
        ];
    }

    /**
     * Get student balance for a specific year.
     *
     * @param int $studentId
     * @param int $year
     * @return string Balance as decimal string
     */
    public function getStudentBalanceForYear(int $studentId, int $year): string
    {
        $result = $this->getStudentBalance($studentId, $year);

        return $result['balance'];
    }

    /**
     * Get all students with outstanding balance for a year.
     *
     * @param int $year
     * @return Collection Students with balance > 0
     */
    public function getOutstandingStudentsForYear(int $year): Collection
    {
        // Get all active invoices for the year with outstanding balance
        $invoices = StudentInvoice::forYear($year)
            ->active()
            ->where('balance', '>', 0)
            ->with(['student', 'student.currentGrade'])
            ->get();

        // Group by student and calculate totals
        $studentBalances = $invoices->groupBy('student_id')->map(function ($studentInvoices) {
            $student = $studentInvoices->first()->student;
            $totalBalance = '0.00';
            $invoiceCount = $studentInvoices->count();

            foreach ($studentInvoices as $invoice) {
                $totalBalance = bcadd($totalBalance, (string) $invoice->balance, 2);
            }

            return [
                'student_id' => $student->id,
                'student_name' => $student->full_name ?? ($student->first_name . ' ' . $student->last_name),
                'balance' => $totalBalance,
                'invoice_count' => $invoiceCount,
                'student' => $student,
            ];
        });

        return $studentBalances->values();
    }

    /**
     * Get the previous year's balance for a student.
     *
     * @param int $studentId
     * @param int $year Current year to find previous from
     * @return string Balance from previous year, or '0.00' if none
     */
    public function getPreviousYearBalance(int $studentId, int $year): string
    {
        $previousYear = $year - 1;

        return $this->getStudentBalanceForYear($studentId, $previousYear);
    }

    /**
     * Carry forward balance from one year to another.
     *
     * @param Student $student
     * @param int $fromYear
     * @param int $toYear
     * @param User $user
     * @return FeeBalanceCarryover|null Returns null if no balance or already carried over
     */
    public function carryForwardBalance(Student $student, int $fromYear, int $toYear, User $user): ?FeeBalanceCarryover
    {
        return DB::transaction(function () use ($student, $fromYear, $toYear, $user) {
            // Check if carryover already exists to prevent duplicates
            $existingCarryover = FeeBalanceCarryover::forStudent($student->id)
                ->forYearRange($fromYear, $toYear)
                ->exists();

            if ($existingCarryover) {
                return null;
            }

            // Calculate balance from the source year
            $balance = $this->getStudentBalanceForYear($student->id, $fromYear);

            // If no balance to carry, return null
            if (bccomp($balance, '0.00', 2) <= 0) {
                return null;
            }

            // Create carryover record
            $carryover = FeeBalanceCarryover::create([
                'student_id' => $student->id,
                'from_year' => $fromYear,
                'to_year' => $toYear,
                'balance_amount' => $balance,
                'carried_at' => now(),
                'carried_by' => $user->id,
            ]);

            // Log to audit trail
            FeeAuditLog::log(
                $carryover,
                FeeAuditLog::ACTION_CARRYOVER,
                null,
                $carryover->toArray(),
                "Balance carried forward for student ID: {$student->id}, Amount: {$balance}, From year: {$fromYear} to year: {$toYear}"
            );

            return $carryover;
        });
    }

    /**
     * Check if student is cleared for a year.
     *
     * @param int $studentId
     * @param int $year
     * @return array{cleared: bool, balance: string, has_override: bool, override_reason: ?string}
     */
    public function checkYearClearance(int $studentId, int $year): array
    {
        $balance = $this->getStudentBalanceForYear($studentId, $year);

        // Check for override
        $clearance = StudentClearance::forStudent($studentId)
            ->forYear($year)
            ->overrideGranted()
            ->first();

        $hasOverride = $clearance !== null;
        $overrideReason = $hasOverride ? $clearance->reason : null;

        // Cleared if balance is zero or override exists
        $cleared = bccomp($balance, '0.00', 2) === 0 || $hasOverride;

        return [
            'cleared' => $cleared,
            'balance' => $balance,
            'has_override' => $hasOverride,
            'override_reason' => $overrideReason,
        ];
    }

    /**
     * Grant a clearance override for a student.
     *
     * @param int $studentId
     * @param int $year
     * @param User $grantedBy
     * @param string $reason
     * @param string|null $notes
     * @return StudentClearance
     */
    public function grantClearanceOverride(int $studentId, int $year, User $grantedBy, string $reason, ?string $notes = null): StudentClearance
    {
        return DB::transaction(function () use ($studentId, $year, $grantedBy, $reason, $notes) {
            // Create or update clearance record
            $clearance = StudentClearance::updateOrCreate(
                [
                    'student_id' => $studentId,
                    'year' => $year,
                ],
                [
                    'override_granted' => true,
                    'granted_by' => $grantedBy->id,
                    'granted_at' => now(),
                    'reason' => $reason,
                    'notes' => $notes,
                ]
            );

            // Log to audit trail
            FeeAuditLog::log(
                $clearance,
                FeeAuditLog::ACTION_CREATE,
                null,
                $clearance->toArray(),
                "Clearance override granted for student ID: {$studentId}, Year: {$year}. Reason: {$reason}"
            );

            return $clearance;
        });
    }

    /**
     * Revoke a clearance override.
     *
     * @param int $studentId
     * @param int $year
     * @param User $revokedBy
     * @param string $reason
     * @return bool
     */
    public function revokeClearanceOverride(int $studentId, int $year, User $revokedBy, string $reason): bool
    {
        return DB::transaction(function () use ($studentId, $year, $revokedBy, $reason) {
            $clearance = StudentClearance::forStudent($studentId)
                ->forYear($year)
                ->first();

            if (!$clearance || !$clearance->override_granted) {
                return false;
            }

            $oldValues = $clearance->toArray();

            // Revoke the override
            $clearance->update([
                'override_granted' => false,
            ]);

            // Log to audit trail
            FeeAuditLog::log(
                $clearance,
                FeeAuditLog::ACTION_UPDATE,
                $oldValues,
                $clearance->fresh()->toArray(),
                "Clearance override revoked for student ID: {$studentId}, Year: {$year}. Reason: {$reason}"
            );

            return true;
        });
    }
}
