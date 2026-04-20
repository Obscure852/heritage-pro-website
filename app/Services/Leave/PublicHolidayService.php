<?php

namespace App\Services\Leave;

use App\Models\Leave\PublicHoliday;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Service class for managing public holidays.
 *
 * Provides CRUD operations and utility methods for public holiday management.
 * Critical methods for leave calculation integration: getHolidayDatesForYear, isHoliday, countHolidaysBetween.
 */
class PublicHolidayService {
    /**
     * Get all public holidays ordered by date.
     *
     * @return Collection
     */
    public function getAll(): Collection {
        return PublicHoliday::orderBy('date')->get();
    }

    /**
     * Get holidays for a specific year.
     * Includes recurring holidays regardless of their stored year.
     *
     * @param int $year
     * @return Collection
     */
    public function getForYear(int $year): Collection {
        return PublicHoliday::forYear($year)
            ->orderBy('date')
            ->get()
            ->map(function ($holiday) use ($year) {
                // For recurring holidays, adjust the date to the requested year
                if ($holiday->is_recurring) {
                    $holiday->display_date = Carbon::create(
                        $year,
                        $holiday->date->month,
                        $holiday->date->day
                    );
                } else {
                    $holiday->display_date = $holiday->date;
                }
                return $holiday;
            })
            ->sortBy('display_date')
            ->values();
    }

    /**
     * Get only active public holidays.
     *
     * @return Collection
     */
    public function getActive(): Collection {
        return PublicHoliday::active()
            ->orderBy('date')
            ->get();
    }

    /**
     * Get active holidays for a specific year.
     *
     * @param int $year
     * @return Collection
     */
    public function getActiveForYear(int $year): Collection {
        return PublicHoliday::active()
            ->forYear($year)
            ->orderBy('date')
            ->get()
            ->map(function ($holiday) use ($year) {
                if ($holiday->is_recurring) {
                    $holiday->display_date = Carbon::create(
                        $year,
                        $holiday->date->month,
                        $holiday->date->day
                    );
                } else {
                    $holiday->display_date = $holiday->date;
                }
                return $holiday;
            })
            ->sortBy('display_date')
            ->values();
    }

    /**
     * Find a holiday by ID or throw exception.
     *
     * @param int $id
     * @return PublicHoliday
     * @throws ModelNotFoundException
     */
    public function findById(int $id): PublicHoliday {
        return PublicHoliday::findOrFail($id);
    }

    /**
     * Create a new public holiday.
     *
     * @param array $data
     * @return PublicHoliday
     */
    public function create(array $data): PublicHoliday {
        return DB::transaction(function () use ($data) {
            return PublicHoliday::create([
                'name' => $data['name'],
                'date' => $data['date'],
                'is_recurring' => $data['is_recurring'] ?? false,
                'description' => $data['description'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);
        });
    }

    /**
     * Update an existing public holiday.
     *
     * @param PublicHoliday $holiday
     * @param array $data
     * @return PublicHoliday
     */
    public function update(PublicHoliday $holiday, array $data): PublicHoliday {
        return DB::transaction(function () use ($holiday, $data) {
            $holiday->update([
                'name' => $data['name'] ?? $holiday->name,
                'date' => $data['date'] ?? $holiday->date,
                'is_recurring' => $data['is_recurring'] ?? $holiday->is_recurring,
                'description' => array_key_exists('description', $data) ? $data['description'] : $holiday->description,
                'is_active' => $data['is_active'] ?? $holiday->is_active,
            ]);

            return $holiday->fresh();
        });
    }

    /**
     * Delete a public holiday.
     *
     * @param PublicHoliday $holiday
     * @return bool
     */
    public function delete(PublicHoliday $holiday): bool {
        return DB::transaction(function () use ($holiday) {
            return $holiday->delete();
        });
    }

    /**
     * Toggle the active status of a holiday.
     *
     * @param PublicHoliday $holiday
     * @return PublicHoliday
     */
    public function toggleStatus(PublicHoliday $holiday): PublicHoliday {
        return DB::transaction(function () use ($holiday) {
            $holiday->update([
                'is_active' => !$holiday->is_active,
            ]);

            return $holiday->fresh();
        });
    }

    /**
     * Get array of Carbon dates for all active holidays in a year.
     * Critical for Phase 5 leave day calculations.
     *
     * @param int $year
     * @return array Array of Carbon dates
     */
    public function getHolidayDatesForYear(int $year): array {
        $holidays = $this->getActiveForYear($year);

        return $holidays->map(function ($holiday) use ($year) {
            if ($holiday->is_recurring) {
                return Carbon::create($year, $holiday->date->month, $holiday->date->day);
            }
            return $holiday->date;
        })->toArray();
    }

    /**
     * Check if a specific date is a public holiday.
     *
     * @param Carbon $date
     * @return bool
     */
    public function isHoliday(Carbon $date): bool {
        // Check non-recurring holidays for exact date match
        $exactMatch = PublicHoliday::active()
            ->where('is_recurring', false)
            ->whereDate('date', $date)
            ->exists();

        if ($exactMatch) {
            return true;
        }

        // Check recurring holidays by month and day
        $recurringMatch = PublicHoliday::active()
            ->where('is_recurring', true)
            ->whereMonth('date', $date->month)
            ->whereDay('date', $date->day)
            ->exists();

        return $recurringMatch;
    }

    /**
     * Count the number of public holidays between two dates.
     * Critical for Phase 5 leave day calculations.
     *
     * @param Carbon $start
     * @param Carbon $end
     * @return int
     */
    public function countHolidaysBetween(Carbon $start, Carbon $end): int {
        $count = 0;
        $current = $start->copy();

        while ($current->lte($end)) {
            if ($this->isHoliday($current)) {
                $count++;
            }
            $current->addDay();
        }

        return $count;
    }

    /**
     * Get holidays grouped by month for calendar display.
     *
     * @param int $year
     * @return Collection
     */
    public function getHolidaysGroupedByMonth(int $year): Collection {
        $holidays = $this->getActiveForYear($year);

        // Initialize all months
        $grouped = collect();
        for ($month = 1; $month <= 12; $month++) {
            $grouped->put($month, collect());
        }

        // Group holidays by month
        foreach ($holidays as $holiday) {
            $displayDate = $holiday->display_date ?? $holiday->date;
            $month = $displayDate->month;
            $grouped[$month]->push($holiday);
        }

        return $grouped;
    }

    /**
     * Check if a holiday with the same date exists.
     *
     * @param string $date
     * @param int|null $excludeId ID to exclude (for updates)
     * @return bool
     */
    public function holidayExistsOnDate(string $date, ?int $excludeId = null): bool {
        $query = PublicHoliday::whereDate('date', $date);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}
