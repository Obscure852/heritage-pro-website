<?php

namespace App\Services\Leave;

use App\Helpers\TermHelper;
use App\Models\Leave\LeaveAuditLog;
use App\Models\Leave\LeaveBalance;
use App\Models\Leave\LeaveBalanceAdjustment;
use App\Models\Leave\LeavePolicy;
use App\Models\Leave\LeaveSetting;
use App\Models\Leave\LeaveType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Service for managing leave balances.
 *
 * Handles balance initialization, allocation, accrual, adjustments, and carry-over processing.
 * This is the core business logic layer for the leave balance system.
 */
class LeaveBalanceService {
    /**
     * @var LeavePolicyService
     */
    protected LeavePolicyService $leavePolicyService;

    /**
     * Create a new service instance.
     *
     * @param LeavePolicyService $leavePolicyService
     */
    public function __construct(LeavePolicyService $leavePolicyService) {
        $this->leavePolicyService = $leavePolicyService;
    }

    // ==================== BALANCE INITIALIZATION ====================

    /**
     * Initialize balance records for all active staff for all active leave types for the given year.
     *
     * Creates balance records with zero values. Idempotent - skips if balance already exists.
     *
     * @param int $year The leave year to initialize
     * @return int Count of balances created
     */
    public function initializeBalancesForYear(int $year): array {
        $leaveTypes = LeaveType::active()->get();
        $allLeaveTypes = LeaveType::all(); // Include inactive for cleanup
        $users = User::where('status', 'Current')->get();

        $stats = [
            'created' => 0,
            'removed' => 0,
            'updated' => 0,
        ];

        // Step 1: Create new balances for users who should have them
        foreach ($users as $user) {
            foreach ($leaveTypes as $leaveType) {
                // Check gender restriction using the helper method
                if (!$leaveType->isGenderEligible($user->gender)) {
                    continue;
                }

                // Check if balance already exists
                $existingBalance = LeaveBalance::forUser($user->id)
                    ->forYear($year)
                    ->forType($leaveType->id)
                    ->first();

                if (!$existingBalance) {
                    $balance = LeaveBalance::create([
                        'user_id' => $user->id,
                        'leave_type_id' => $leaveType->id,
                        'leave_year' => $year,
                        'entitled' => 0,
                        'carried_over' => 0,
                        'accrued' => 0,
                        'used' => 0,
                        'pending' => 0,
                        'adjusted' => 0,
                    ]);

                    // Allocate the balance based on leave type policy
                    $this->allocateBalance($balance);
                    $stats['created']++;
                }
            }
        }

        // Step 2: Remove balances that no longer qualify due to gender restriction changes
        // Only remove if user has not used any leave (used = 0 and pending = 0)
        foreach ($allLeaveTypes as $leaveType) {
            if ($leaveType->gender_restriction === null) {
                continue; // No restriction, nothing to clean up
            }

            // Get all balances for this leave type and check eligibility using the helper
            $balancesForType = LeaveBalance::forYear($year)
                ->forType($leaveType->id)
                ->where('used', 0)
                ->where('pending', 0)
                ->with('user')
                ->get();

            foreach ($balancesForType as $balance) {
                // Use the helper method to check gender eligibility
                if (!$leaveType->isGenderEligible($balance->user->gender)) {
                    $balance->delete();
                    $stats['removed']++;
                }
            }
        }

        // Step 3: Remove balances for inactive leave types (if no usage)
        $inactiveTypeIds = LeaveType::where('is_active', false)->pluck('id');
        if ($inactiveTypeIds->isNotEmpty()) {
            $inactiveBalances = LeaveBalance::forYear($year)
                ->whereIn('leave_type_id', $inactiveTypeIds)
                ->where('used', 0)
                ->where('pending', 0)
                ->get();

            foreach ($inactiveBalances as $balance) {
                $balance->delete();
                $stats['removed']++;
            }
        }

        // Step 4: Re-sync entitlements for existing balances that may have outdated values
        // Only update if the user hasn't used any leave yet (to preserve their original allocation)
        foreach ($users as $user) {
            foreach ($leaveTypes as $leaveType) {
                // Check gender restriction using the helper method
                if (!$leaveType->isGenderEligible($user->gender)) {
                    continue;
                }

                $balance = LeaveBalance::forUser($user->id)
                    ->forYear($year)
                    ->forType($leaveType->id)
                    ->first();

                // Only re-allocate if balance exists, has no usage, and entitled is 0 or needs update
                if ($balance && $balance->used == 0 && $balance->pending == 0 && $balance->entitled == 0) {
                    $this->allocateBalance($balance);
                    $stats['updated']++;
                }
            }
        }

        return $stats;
    }

    /**
     * Initialize balance records for a single user for all active leave types.
     *
     * @param User $user The user to initialize balances for
     * @param int $year The leave year
     * @return Collection Collection of created/existing balances
     */
    public function initializeBalanceForUser(User $user, int $year): Collection {
        $leaveTypes = LeaveType::active()->get();
        $balances = collect();

        foreach ($leaveTypes as $leaveType) {
            // Check gender restriction using the helper method
            if (!$leaveType->isGenderEligible($user->gender)) {
                continue;
            }

            $balance = $this->getOrCreateBalance($user, $leaveType, $year);
            $balances->push($balance);
        }

        return $balances;
    }

    // ==================== ALLOCATION MODE ====================

    /**
     * Allocate balance for allocation mode leave types.
     *
     * Sets entitled to the leave type's default_entitlement. Prorates for new employees
     * if policy specifies prorate_new_employees and user started mid-year.
     *
     * @param LeaveBalance $balance The balance record to allocate
     * @return LeaveBalance The updated balance
     */
    public function allocateBalance(LeaveBalance $balance): LeaveBalance {
        return DB::transaction(function () use ($balance) {
            $leaveType = $balance->leaveType;
            $policy = $this->leavePolicyService->getPolicyForTypeAndYear(
                $balance->leave_type_id,
                $balance->leave_year
            );

            // Only allocate for allocation mode types
            if ($policy && $policy->balance_mode !== LeavePolicy::MODE_ALLOCATION) {
                return $balance;
            }

            // Capture old values for audit log (AUDT-01)
            $oldValues = $balance->toArray();

            $entitlement = (float) $leaveType->default_entitlement;

            // Prorate for new employees if enabled
            if ($policy && $policy->prorate_new_employees) {
                $user = $balance->user;
                $hireDate = Carbon::parse($user->created_at);
                $entitlement = $this->prorateEntitlement($entitlement, $hireDate, $balance->leave_year);
            }

            $balance->entitled = $entitlement;
            $balance->save();

            // Log audit entry for balance allocation (AUDT-01)
            LeaveAuditLog::log(
                $balance,
                LeaveAuditLog::ACTION_ALLOCATE,
                $oldValues,
                $balance->fresh()->toArray(),
                'Annual allocation: ' . number_format($entitlement, 1) . ' days'
            );

            return $balance->fresh();
        });
    }

    /**
     * Batch allocate balances for all users with allocation mode leave types for a year.
     *
     * @param int $year The leave year
     * @return int Count of balances updated
     */
    public function allocateAllForYear(int $year): int {
        $updatedCount = 0;

        // Get all leave types with allocation mode policies for this year
        $policies = LeavePolicy::forYear($year)
            ->where('balance_mode', LeavePolicy::MODE_ALLOCATION)
            ->get();

        $leaveTypeIds = $policies->pluck('leave_type_id')->toArray();

        if (empty($leaveTypeIds)) {
            return 0;
        }

        // Get all balances for these leave types in this year
        $balances = LeaveBalance::forYear($year)
            ->whereIn('leave_type_id', $leaveTypeIds)
            ->with(['leaveType', 'user'])
            ->get();

        foreach ($balances as $balance) {
            $this->allocateBalance($balance);
            $updatedCount++;
        }

        return $updatedCount;
    }

    // ==================== BALANCE RETRIEVAL ====================

    /**
     * Get a single balance for a user, leave type, and year.
     *
     * @param int $userId
     * @param int $leaveTypeId
     * @param int $year
     * @return LeaveBalance|null
     */
    public function getBalanceForUser(int $userId, int $leaveTypeId, int $year): ?LeaveBalance {
        return LeaveBalance::forUser($userId)
            ->forYear($year)
            ->forType($leaveTypeId)
            ->first();
    }

    /**
     * Get all balances for a user in a specific year.
     *
     * @param int $userId
     * @param int $year
     * @return Collection
     */
    public function getBalancesForUser(int $userId, int $year): Collection {
        return LeaveBalance::forUser($userId)
            ->forYear($year)
            ->with('leaveType')
            ->get();
    }

    /**
     * Get an existing balance or create a new one with zero values.
     *
     * @param User $user
     * @param LeaveType $leaveType
     * @param int $year
     * @return LeaveBalance
     */
    public function getOrCreateBalance(User $user, LeaveType $leaveType, int $year): LeaveBalance {
        $balance = LeaveBalance::forUser($user->id)
            ->forYear($year)
            ->forType($leaveType->id)
            ->first();

        if ($balance) {
            return $balance;
        }

        return LeaveBalance::create([
            'user_id' => $user->id,
            'leave_type_id' => $leaveType->id,
            'leave_year' => $year,
            'entitled' => 0,
            'carried_over' => 0,
            'accrued' => 0,
            'used' => 0,
            'pending' => 0,
            'adjusted' => 0,
        ]);
    }

    // ==================== HELPER METHODS ====================

    /**
     * Calculate the current leave year based on the leave_year_start_month setting.
     *
     * If current month >= start month, leave year = current calendar year.
     * Otherwise, leave year = previous calendar year.
     *
     * @return int The current leave year
     */
    public function getCurrentLeaveYear(): int {
        // Use TermHelper to get the current term's year
        $currentTerm = TermHelper::getCurrentTerm();

        if ($currentTerm) {
            return (int) $currentTerm->year;
        }

        // Fallback to system date calculation if no current term
        $startMonth = $this->leavePolicyService->getLeaveYearStartMonth();
        $currentMonth = (int) date('n');
        $currentYear = (int) date('Y');

        if ($currentMonth >= $startMonth) {
            return $currentYear;
        }

        return $currentYear - 1;
    }

    /**
     * Calculate prorated entitlement based on months remaining in leave year from hire date.
     *
     * @param float $fullEntitlement The full annual entitlement
     * @param Carbon $hireDate The employee's hire date
     * @param int $leaveYear The leave year for calculation
     * @return float Prorated entitlement rounded to 1 decimal place
     */
    public function prorateEntitlement(float $fullEntitlement, Carbon $hireDate, int $leaveYear): float {
        $startMonth = $this->leavePolicyService->getLeaveYearStartMonth();

        // Calculate leave year start and end dates
        $leaveYearStart = Carbon::create($leaveYear, $startMonth, 1)->startOfMonth();
        $leaveYearEnd = $leaveYearStart->copy()->addYear()->subDay();

        // If hired before leave year starts, return full entitlement
        if ($hireDate->lt($leaveYearStart)) {
            return $fullEntitlement;
        }

        // If hired after leave year ends, return 0
        if ($hireDate->gt($leaveYearEnd)) {
            return 0.0;
        }

        // Calculate months remaining from hire date to leave year end
        // +1 because we include the hire month
        $monthsRemaining = $hireDate->diffInMonths($leaveYearEnd) + 1;

        // Calculate prorated amount
        $prorated = ($fullEntitlement / 12) * $monthsRemaining;

        return round($prorated, 1);
    }

    // ==================== ACCRUAL MODE ====================

    /**
     * Accrue balance for accrual mode leave types.
     *
     * Adds the policy's accrual_rate to the balance's accrued field.
     * Called monthly by scheduled job.
     *
     * @param LeaveBalance $balance The balance record to accrue
     * @return LeaveBalance The updated balance
     */
    public function accrueBalance(LeaveBalance $balance): LeaveBalance {
        return DB::transaction(function () use ($balance) {
            $policy = $this->leavePolicyService->getPolicyForTypeAndYear(
                $balance->leave_type_id,
                $balance->leave_year
            );

            // Only accrue for accrual mode types with a defined rate
            if (!$policy || $policy->balance_mode !== LeavePolicy::MODE_ACCRUAL) {
                return $balance;
            }

            if ($policy->accrual_rate === null || $policy->accrual_rate <= 0) {
                return $balance;
            }

            $balance->accrued = (float) $balance->accrued + (float) $policy->accrual_rate;
            $balance->save();

            return $balance->fresh();
        });
    }

    /**
     * Batch accrue balances for all users with accrual mode leave types for a month.
     *
     * Idempotent - uses last_accrued_month tracking via a simple date check.
     * Should only accrue once per month per balance.
     *
     * @param int $year The leave year
     * @param int $month The month (1-12)
     * @return int Count of balances updated
     */
    public function accrueAllForMonth(int $year, int $month): int {
        $updatedCount = 0;

        // Get all leave types with accrual mode policies for this year
        $policies = LeavePolicy::forYear($year)
            ->where('balance_mode', LeavePolicy::MODE_ACCRUAL)
            ->whereNotNull('accrual_rate')
            ->where('accrual_rate', '>', 0)
            ->get();

        $leaveTypeIds = $policies->pluck('leave_type_id')->toArray();

        if (empty($leaveTypeIds)) {
            return 0;
        }

        // Get all balances for these leave types in this year
        // We'll check updated_at to implement idempotency
        $currentMonthStart = Carbon::create($year, $month, 1)->startOfMonth();
        $currentMonthEnd = $currentMonthStart->copy()->endOfMonth();

        $balances = LeaveBalance::forYear($year)
            ->whereIn('leave_type_id', $leaveTypeIds)
            ->get();

        foreach ($balances as $balance) {
            // Check if already accrued this month (simple idempotency via date check)
            // If balance was last updated within this month and accrued > 0, skip
            $lastUpdated = Carbon::parse($balance->updated_at);
            if ($lastUpdated->between($currentMonthStart, $currentMonthEnd)
                && $balance->accrued > 0) {
                // Likely already accrued this month - but this is a simple check
                // For production, consider adding a dedicated last_accrued_month column
                continue;
            }

            $this->accrueBalance($balance);
            $updatedCount++;
        }

        return $updatedCount;
    }

    // ==================== MANUAL ADJUSTMENT ====================

    /**
     * Adjust a leave balance manually.
     *
     * Creates an adjustment record and updates the balance's adjusted field.
     * For debit, subtracts from adjusted; for credit/correction, adds to adjusted.
     *
     * @param LeaveBalance $balance The balance to adjust
     * @param string $type Adjustment type (LeaveBalanceAdjustment::TYPE_* constant)
     * @param float $days Number of days to adjust
     * @param string $reason Reason for adjustment
     * @param int $adjustedBy User ID of person making adjustment
     * @return LeaveBalance The updated balance
     * @throws InvalidArgumentException If adjustment is invalid
     */
    public function adjustBalance(
        LeaveBalance $balance,
        string $type,
        float $days,
        string $reason,
        int $adjustedBy
    ): LeaveBalance {
        return DB::transaction(function () use ($balance, $type, $days, $reason, $adjustedBy) {
            // Validate adjustment type
            $validTypes = [
                LeaveBalanceAdjustment::TYPE_CREDIT,
                LeaveBalanceAdjustment::TYPE_DEBIT,
                LeaveBalanceAdjustment::TYPE_CORRECTION,
            ];

            if (!in_array($type, $validTypes)) {
                throw new InvalidArgumentException("Invalid adjustment type: {$type}");
            }

            // Validate adjustment won't cause negative balance (unless allowed)
            if (!$this->validateAdjustment($balance, $type, $days)) {
                throw new InvalidArgumentException(
                    'Adjustment would result in negative balance which is not allowed for this leave type.'
                );
            }

            // Capture old values for audit log (AUDT-01)
            $oldValues = $balance->toArray();

            // Calculate adjustment amount
            $adjustmentAmount = $days;
            if ($type === LeaveBalanceAdjustment::TYPE_DEBIT) {
                $adjustmentAmount = -$days;
            }

            // Create adjustment record
            LeaveBalanceAdjustment::create([
                'leave_balance_id' => $balance->id,
                'adjustment_type' => $type,
                'days' => $days,
                'reason' => $reason,
                'adjusted_by' => $adjustedBy,
            ]);

            // Update balance
            $balance->adjusted = (float) $balance->adjusted + $adjustmentAmount;
            $balance->save();

            // Log audit entry for balance adjustment (AUDT-01)
            $sign = $type === LeaveBalanceAdjustment::TYPE_DEBIT ? '-' : '+';
            LeaveAuditLog::log(
                $balance,
                LeaveAuditLog::ACTION_ADJUST,
                $oldValues,
                $balance->fresh()->toArray(),
                "Adjustment: {$type} {$sign}" . number_format($days, 1) . " days - {$reason}"
            );

            return $balance->fresh();
        });
    }

    /**
     * Get adjustment history for a balance.
     *
     * @param LeaveBalance $balance
     * @return Collection Adjustments ordered by created_at desc
     */
    public function getAdjustmentHistory(LeaveBalance $balance): Collection {
        return $balance->adjustments()
            ->with('adjustedBy')
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Validate that an adjustment is allowed.
     *
     * Returns true if adjustment won't cause negative balance or if leave type allows negative.
     *
     * @param LeaveBalance $balance
     * @param string $type Adjustment type
     * @param float $days Days to adjust
     * @return bool True if adjustment is valid
     */
    public function validateAdjustment(LeaveBalance $balance, string $type, float $days): bool {
        // Credit and correction always increase or maintain balance
        if ($type !== LeaveBalanceAdjustment::TYPE_DEBIT) {
            return true;
        }

        // Check if leave type allows negative balance
        $leaveType = $balance->leaveType;
        if ($leaveType && $leaveType->allow_negative_balance) {
            return true;
        }

        // Check if debit would cause negative available balance
        $projectedAvailable = $balance->available - $days;

        return $projectedAvailable >= 0;
    }

    // ==================== YEAR-END CARRY-OVER ====================

    /**
     * Process carry-over from previous year balance to new year.
     *
     * Calculates carry-over based on policy:
     * - 'none': carried_over = 0
     * - 'limited': carried_over = min(available, carry_over_limit)
     * - 'full': carried_over = available (capped at entitled to prevent runaway accumulation)
     *
     * @param LeaveBalance $previousYearBalance Balance from previous year
     * @param int $newYear The new leave year
     * @return LeaveBalance The new year balance with carry-over applied
     */
    public function processCarryOver(LeaveBalance $previousYearBalance, int $newYear): LeaveBalance {
        return DB::transaction(function () use ($previousYearBalance, $newYear) {
            $user = $previousYearBalance->user;
            $leaveType = $previousYearBalance->leaveType;
            $fromYear = $previousYearBalance->leave_year;

            // Get or create balance for new year
            $newYearBalance = $this->getOrCreateBalance($user, $leaveType, $newYear);

            // Capture old values for audit log (AUDT-01)
            $oldValues = $newYearBalance->toArray();

            // Get policy for the new year (carry-over rules apply to receiving year)
            $policy = $this->leavePolicyService->getPolicyForTypeAndYear(
                $leaveType->id,
                $newYear
            );

            // Calculate carry-over amount based on policy
            $carryOverAmount = 0.0;
            $available = $previousYearBalance->available;

            if (!$policy || $policy->carry_over_mode === LeavePolicy::CARRY_NONE) {
                $carryOverAmount = 0.0;
            } elseif ($policy->carry_over_mode === LeavePolicy::CARRY_LIMITED) {
                $limit = (float) ($policy->carry_over_limit ?? 0);
                $carryOverAmount = min($available, $limit);
            } elseif ($policy->carry_over_mode === LeavePolicy::CARRY_FULL) {
                // Cap at entitled to prevent runaway accumulation
                $maxCarryOver = (float) $previousYearBalance->entitled;
                $carryOverAmount = min($available, $maxCarryOver);
            }

            // Ensure non-negative
            $carryOverAmount = max(0.0, $carryOverAmount);

            // Update new year balance
            $newYearBalance->carried_over = $carryOverAmount;
            $newYearBalance->save();

            // Log audit entry for carry-over (AUDT-01)
            LeaveAuditLog::log(
                $newYearBalance,
                LeaveAuditLog::ACTION_CARRYOVER,
                $oldValues,
                $newYearBalance->fresh()->toArray(),
                "Carry-over from year {$fromYear}: " . number_format($carryOverAmount, 1) . ' days'
            );

            return $newYearBalance->fresh();
        });
    }

    /**
     * Batch process carry-overs for all users and leave types from one year to another.
     *
     * @param int $fromYear Source year
     * @param int $toYear Target year
     * @return int Count of carry-overs processed
     */
    public function processAllCarryOvers(int $fromYear, int $toYear): int {
        $processedCount = 0;

        // Get all balances from the source year
        $previousYearBalances = LeaveBalance::forYear($fromYear)
            ->with(['user', 'leaveType'])
            ->get();

        foreach ($previousYearBalances as $previousBalance) {
            // Skip if user is no longer active
            if ($previousBalance->user->status !== 'Current') {
                continue;
            }

            // Skip if leave type is no longer active
            if (!$previousBalance->leaveType->is_active) {
                continue;
            }

            $this->processCarryOver($previousBalance, $toYear);
            $processedCount++;
        }

        return $processedCount;
    }

    // ==================== BALANCE CALCULATION ====================

    /**
     * Calculate available balance for a balance record.
     *
     * Simple wrapper around the model's computed attribute.
     *
     * @param LeaveBalance $balance
     * @return float Available balance
     */
    public function calculateAvailable(LeaveBalance $balance): float {
        return (float) $balance->available;
    }
}
