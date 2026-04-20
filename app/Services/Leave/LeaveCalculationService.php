<?php

namespace App\Services\Leave;

use App\Models\Leave\LeaveSetting;
use Carbon\Carbon;

/**
 * Service for calculating leave days.
 *
 * Handles complex leave day calculations including:
 * - Weekend exclusion (configurable)
 * - Public holiday exclusion
 * - Half-day calculations
 *
 * Critical for leave request validation and balance management.
 */
class LeaveCalculationService {
    protected PublicHolidayService $publicHolidayService;

    /**
     * Create a new LeaveCalculationService instance.
     *
     * @param PublicHolidayService $publicHolidayService
     */
    public function __construct(PublicHolidayService $publicHolidayService) {
        $this->publicHolidayService = $publicHolidayService;
    }

    /**
     * Calculate the total leave days for a date range.
     *
     * Excludes weekends and public holidays from the count.
     * Supports half-day calculations for start and end dates.
     *
     * @param Carbon $startDate Start date of leave
     * @param Carbon $endDate End date of leave
     * @param string|null $startHalfDay 'am' or 'pm' to indicate only half of start day
     * @param string|null $endHalfDay 'am' or 'pm' to indicate only half of end day
     * @return float Total leave days (supports 0.5 increments)
     */
    public function calculateLeaveDays(
        Carbon $startDate,
        Carbon $endDate,
        ?string $startHalfDay = null,
        ?string $endHalfDay = null
    ): float {
        // Normalize dates to start of day
        $start = $startDate->copy()->startOfDay();
        $end = $endDate->copy()->startOfDay();

        // Handle same day request
        if ($start->equalTo($end)) {
            return $this->calculateSameDayLeave($start, $startHalfDay, $endHalfDay);
        }

        // Calculate working days in range
        $totalDays = 0.0;
        $current = $start->copy();

        while ($current->lte($end)) {
            if ($this->isWorkingDay($current)) {
                // Determine day value (full or half)
                $dayValue = 1.0;

                if ($current->equalTo($start) && $startHalfDay !== null) {
                    // Start day with half-day: only count half
                    $dayValue = 0.5;
                } elseif ($current->equalTo($end) && $endHalfDay !== null) {
                    // End day with half-day: only count half
                    $dayValue = 0.5;
                }

                $totalDays += $dayValue;
            }

            $current->addDay();
        }

        return $totalDays;
    }

    /**
     * Calculate leave days for a same-day request.
     *
     * @param Carbon $date The leave date
     * @param string|null $startHalfDay 'am' or 'pm' for half-day start
     * @param string|null $endHalfDay 'am' or 'pm' for half-day end
     * @return float 0.0, 0.5, or 1.0
     */
    protected function calculateSameDayLeave(
        Carbon $date,
        ?string $startHalfDay,
        ?string $endHalfDay
    ): float {
        // If it's not a working day, return 0
        if (!$this->isWorkingDay($date)) {
            return 0.0;
        }

        // Both halves specified on same day = full day
        if ($startHalfDay !== null && $endHalfDay !== null) {
            return 1.0;
        }

        // Only one half specified = half day
        if ($startHalfDay !== null || $endHalfDay !== null) {
            return 0.5;
        }

        // No half-day specified = full day
        return 1.0;
    }

    /**
     * Check if a date falls on a weekend.
     *
     * Uses configurable weekend days from LeaveSetting.
     *
     * @param Carbon $date Date to check
     * @return bool True if date is a weekend day
     */
    public function isWeekend(Carbon $date): bool {
        $weekendDays = $this->getWeekendDays();
        $dayName = strtolower($date->format('l'));

        return in_array($dayName, $weekendDays, true);
    }

    /**
     * Count the number of weekend days in a date range.
     *
     * @param Carbon $start Start date
     * @param Carbon $end End date
     * @return int Number of weekend days
     */
    public function countWeekends(Carbon $start, Carbon $end): int {
        $count = 0;
        $current = $start->copy()->startOfDay();
        $endDate = $end->copy()->startOfDay();

        while ($current->lte($endDate)) {
            if ($this->isWeekend($current)) {
                $count++;
            }
            $current->addDay();
        }

        return $count;
    }

    /**
     * Count the number of public holidays in a date range.
     *
     * Delegates to PublicHolidayService.
     *
     * @param Carbon $start Start date
     * @param Carbon $end End date
     * @return int Number of holidays
     */
    public function countHolidays(Carbon $start, Carbon $end): int {
        return $this->publicHolidayService->countHolidaysBetween($start, $end);
    }

    /**
     * Check if a date is a working day.
     *
     * A working day is neither a weekend nor a public holiday.
     *
     * @param Carbon $date Date to check
     * @return bool True if date is a working day
     */
    public function isWorkingDay(Carbon $date): bool {
        // Check weekend first (cheaper check)
        if ($this->isWeekend($date)) {
            return false;
        }

        // Check holiday
        if ($this->publicHolidayService->isHoliday($date)) {
            return false;
        }

        return true;
    }

    /**
     * Get the configured weekend days.
     *
     * @return array Array of lowercase day names (e.g., ['saturday', 'sunday'])
     */
    protected function getWeekendDays(): array {
        return LeaveSetting::get('weekend_days', ['saturday', 'sunday']);
    }
}
