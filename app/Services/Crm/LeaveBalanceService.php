<?php

namespace App\Services\Crm;

use App\Models\CrmLeaveBalance;
use App\Models\CrmLeaveSetting;
use App\Models\CrmLeaveType;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class LeaveBalanceService
{
    public function getOrCreateBalance(User $user, CrmLeaveType $leaveType, ?int $year = null): CrmLeaveBalance
    {
        $year = $year ?? $this->currentLeaveYear();

        return CrmLeaveBalance::firstOrCreate(
            [
                'user_id' => $user->id,
                'leave_type_id' => $leaveType->id,
                'year' => $year,
            ],
            [
                'entitled_days' => $this->calculateEntitlement($user, $leaveType, $year),
                'carried_over_days' => 0.0,
                'adjustment_days' => 0.0,
                'used_days' => 0.0,
                'pending_days' => 0.0,
            ]
        );
    }

    public function balancesForUser(User $user, ?int $year = null): array
    {
        $year = $year ?? $this->currentLeaveYear();

        $types = CrmLeaveType::active()->forGender($user->gender)->ordered()->get();
        $balances = [];

        foreach ($types as $type) {
            $balances[] = $this->getOrCreateBalance($user, $type, $year);
        }

        return $balances;
    }

    public function reservePendingDays(CrmLeaveBalance $balance, float $days): void
    {
        $balance->increment('pending_days', $days);
    }

    public function releasePendingDays(CrmLeaveBalance $balance, float $days): void
    {
        $balance->decrement('pending_days', min($days, (float) $balance->pending_days));
    }

    public function confirmUsedDays(CrmLeaveBalance $balance, float $days): void
    {
        DB::transaction(function () use ($balance, $days) {
            $balance->decrement('pending_days', min($days, (float) $balance->pending_days));
            $balance->increment('used_days', $days);
        });
    }

    public function reverseUsedDays(CrmLeaveBalance $balance, float $days): void
    {
        $balance->decrement('used_days', min($days, (float) $balance->used_days));
    }

    public function adjustBalance(CrmLeaveBalance $balance, float $adjustment): void
    {
        $balance->update([
            'adjustment_days' => (float) $balance->adjustment_days + $adjustment,
        ]);
    }

    public function hasEnoughBalance(User $user, CrmLeaveType $leaveType, float $days, ?int $year = null): bool
    {
        if ($leaveType->default_days_per_year === null) {
            return true;
        }

        $balance = $this->getOrCreateBalance($user, $leaveType, $year);

        return $balance->effective_available_days >= $days;
    }

    public function currentLeaveYear(): int
    {
        $settings = CrmLeaveSetting::instance();
        $startMonth = $settings->balance_year_start_month;
        $now = now();

        if ($now->month < $startMonth) {
            return $now->year - 1;
        }

        return $now->year;
    }

    public function resetBalancesForYear(int $newYear): int
    {
        $types = CrmLeaveType::active()->get();
        $users = User::where('active', true)->get();
        $count = 0;

        foreach ($users as $user) {
            foreach ($types as $type) {
                DB::transaction(function () use ($user, $type, $newYear) {
                    $previousYear = $newYear - 1;
                    $previousBalance = CrmLeaveBalance::query()
                        ->where('user_id', $user->id)
                        ->where('leave_type_id', $type->id)
                        ->where('year', $previousYear)
                        ->first();

                    $carryOver = 0.0;
                    if ($previousBalance && $type->carry_over_limit !== null) {
                        $remaining = $previousBalance->available_days;
                        $carryOver = min(max($remaining, 0), (float) $type->carry_over_limit);
                    }

                    CrmLeaveBalance::updateOrCreate(
                        [
                            'user_id' => $user->id,
                            'leave_type_id' => $type->id,
                            'year' => $newYear,
                        ],
                        [
                            'entitled_days' => $this->calculateEntitlement($user, $type, $newYear),
                            'carried_over_days' => $carryOver,
                            'adjustment_days' => 0.0,
                            'used_days' => 0.0,
                            'pending_days' => 0.0,
                        ]
                    );
                });

                $count++;
            }
        }

        return $count;
    }

    private function calculateEntitlement(User $user, CrmLeaveType $type, int $year): float
    {
        if ($type->default_days_per_year === null) {
            return 0.0;
        }

        $defaultDays = (float) $type->default_days_per_year;

        if (! $user->date_of_appointment) {
            return $defaultDays;
        }

        $settings = CrmLeaveSetting::instance();
        $yearStart = Carbon::create($year, $settings->balance_year_start_month, 1);
        $yearEnd = $yearStart->copy()->addYear()->subDay();

        if ($user->date_of_appointment->lte($yearStart)) {
            return $defaultDays;
        }

        if ($user->date_of_appointment->gt($yearEnd)) {
            return 0.0;
        }

        $totalMonths = 12;
        $remainingMonths = $yearEnd->diffInMonths($user->date_of_appointment) + 1;

        return round(($defaultDays / $totalMonths) * $remainingMonths, 1);
    }
}
