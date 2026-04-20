<?php

namespace App\Services\Leave;

use App\Models\Leave\LeavePolicy;
use App\Models\Leave\LeaveSetting;
use App\Models\Leave\LeaveType;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Service for managing leave policies.
 *
 * Handles CRUD operations for leave policies with leave year awareness.
 */
class LeavePolicyService {
    /**
     * Get all policies for a specific leave type.
     *
     * @param int $leaveTypeId
     * @return Collection
     */
    public function getPoliciesForType(int $leaveTypeId): Collection {
        return LeavePolicy::forType($leaveTypeId)
            ->orderByDesc('leave_year')
            ->get();
    }

    /**
     * Get all policies for a specific year across all leave types.
     *
     * @param int $year
     * @return Collection
     */
    public function getPoliciesForYear(int $year): Collection {
        return LeavePolicy::forYear($year)
            ->with('leaveType')
            ->get();
    }

    /**
     * Get a specific policy for a leave type and year.
     *
     * @param int $leaveTypeId
     * @param int $year
     * @return LeavePolicy|null
     */
    public function getPolicyForTypeAndYear(int $leaveTypeId, int $year): ?LeavePolicy {
        return LeavePolicy::forType($leaveTypeId)
            ->forYear($year)
            ->first();
    }

    /**
     * Find a policy by ID or fail.
     *
     * @param int $id
     * @return LeavePolicy
     * @throws ModelNotFoundException
     */
    public function findById(int $id): LeavePolicy {
        return LeavePolicy::findOrFail($id);
    }

    /**
     * Create a new leave policy.
     *
     * @param array $data
     * @return LeavePolicy
     */
    public function create(array $data): LeavePolicy {
        return DB::transaction(function () use ($data) {
            // Clean up conditional fields based on modes
            $data = $this->cleanConditionalFields($data);

            return LeavePolicy::create($data);
        });
    }

    /**
     * Update an existing leave policy.
     *
     * @param LeavePolicy $policy
     * @param array $data
     * @return LeavePolicy
     */
    public function update(LeavePolicy $policy, array $data): LeavePolicy {
        return DB::transaction(function () use ($policy, $data) {
            // Clean up conditional fields based on modes
            $data = $this->cleanConditionalFields($data);

            $policy->update($data);

            return $policy->fresh();
        });
    }

    /**
     * Delete a leave policy.
     *
     * @param LeavePolicy $policy
     * @return bool
     */
    public function delete(LeavePolicy $policy): bool {
        return DB::transaction(function () use ($policy) {
            return $policy->delete();
        });
    }

    /**
     * Get the configured leave year start month.
     *
     * @return int The month number (1-12), defaults to 1 (January)
     */
    public function getLeaveYearStartMonth(): int {
        $setting = LeaveSetting::get('leave_year_start_month', ['month' => 1]);

        return (int) ($setting['month'] ?? 1);
    }

    /**
     * Get available years for policy configuration.
     *
     * Returns current year and next 2 years.
     *
     * @return array
     */
    public function getAvailableYears(): array {
        $currentYear = (int) date('Y');

        return [
            $currentYear - 1,
            $currentYear,
            $currentYear + 1,
            $currentYear + 2,
        ];
    }

    /**
     * Get balance mode options.
     *
     * @return array
     */
    public function getBalanceModeOptions(): array {
        return [
            LeavePolicy::MODE_ALLOCATION => 'Allocation (Full balance at year start)',
            LeavePolicy::MODE_ACCRUAL => 'Accrual (Balance accumulates monthly)',
        ];
    }

    /**
     * Get carry-over mode options.
     *
     * @return array
     */
    public function getCarryOverModeOptions(): array {
        return [
            LeavePolicy::CARRY_NONE => 'None (No carry-over)',
            LeavePolicy::CARRY_LIMITED => 'Limited (Up to specified days)',
            LeavePolicy::CARRY_FULL => 'Full (All unused days carry over)',
        ];
    }

    /**
     * Copy policies from one year to another.
     *
     * @param int $fromYear
     * @param int $toYear
     * @param array|null $leaveTypeIds Specific types to copy, null for all
     * @return int Number of policies copied
     */
    public function copyPoliciesFromYear(int $fromYear, int $toYear, ?array $leaveTypeIds = null): int {
        return DB::transaction(function () use ($fromYear, $toYear, $leaveTypeIds) {
            $query = LeavePolicy::forYear($fromYear);

            if ($leaveTypeIds !== null) {
                $query->whereIn('leave_type_id', $leaveTypeIds);
            }

            $sourcePolicies = $query->get();
            $copiedCount = 0;

            foreach ($sourcePolicies as $sourcePolicy) {
                // Check if target policy already exists
                $existingPolicy = LeavePolicy::forType($sourcePolicy->leave_type_id)
                    ->forYear($toYear)
                    ->first();

                if (!$existingPolicy) {
                    $newPolicyData = $sourcePolicy->toArray();
                    unset($newPolicyData['id'], $newPolicyData['created_at'], $newPolicyData['updated_at']);
                    $newPolicyData['leave_year'] = $toYear;

                    LeavePolicy::create($newPolicyData);
                    $copiedCount++;
                }
            }

            return $copiedCount;
        });
    }

    /**
     * Get or create a policy for a leave type and year.
     *
     * Creates a default policy if one doesn't exist.
     *
     * @param LeaveType $leaveType
     * @param int $year
     * @return LeavePolicy
     */
    public function getOrCreatePolicy(LeaveType $leaveType, int $year): LeavePolicy {
        $policy = $this->getPolicyForTypeAndYear($leaveType->id, $year);

        if ($policy) {
            return $policy;
        }

        // Create default policy
        return $this->create([
            'leave_type_id' => $leaveType->id,
            'leave_year' => $year,
            'balance_mode' => LeavePolicy::MODE_ALLOCATION,
            'accrual_rate' => null,
            'carry_over_mode' => LeavePolicy::CARRY_NONE,
            'carry_over_limit' => null,
            'carry_over_expiry_months' => null,
            'prorate_new_employees' => true,
        ]);
    }

    /**
     * Clean up conditional fields based on selected modes.
     *
     * @param array $data
     * @return array
     */
    protected function cleanConditionalFields(array $data): array {
        // Clear accrual_rate if balance_mode is not accrual
        if (isset($data['balance_mode']) && $data['balance_mode'] !== LeavePolicy::MODE_ACCRUAL) {
            $data['accrual_rate'] = null;
        }

        // Clear carry-over limit and expiry if carry_over_mode is not limited
        if (isset($data['carry_over_mode']) && $data['carry_over_mode'] !== LeavePolicy::CARRY_LIMITED) {
            $data['carry_over_limit'] = null;
            $data['carry_over_expiry_months'] = null;
        }

        return $data;
    }
}
