<?php

namespace App\Models\Leave;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Public holiday model.
 *
 * Tracks public holidays for leave calculations.
 * Supports recurring holidays (e.g., Christmas) and one-time dates.
 *
 * @property int $id
 * @property string $name
 * @property \Carbon\Carbon $date
 * @property bool $is_recurring
 * @property string|null $description
 * @property bool $is_active
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
class PublicHoliday extends Model {
    use HasFactory;

    protected $fillable = [
        'name',
        'date',
        'is_recurring',
        'description',
        'is_active',
    ];

    protected $casts = [
        'date' => 'date',
        'is_recurring' => 'boolean',
        'is_active' => 'boolean',
    ];

    // ==================== SCOPES ====================

    /**
     * Scope to only active public holidays.
     */
    public function scopeActive(Builder $query): Builder {
        return $query->where('is_active', true);
    }

    /**
     * Scope to holidays for a specific year.
     * Includes recurring holidays regardless of their stored year.
     */
    public function scopeForYear(Builder $query, int $year): Builder {
        return $query->where(function ($q) use ($year) {
            $q->whereYear('date', $year)
              ->orWhere('is_recurring', true);
        });
    }

    /**
     * Scope to holidays between two dates.
     * Accounts for recurring holidays by checking month/day.
     */
    public function scopeBetween(Builder $query, $start, $end): Builder {
        $startDate = Carbon::parse($start);
        $endDate = Carbon::parse($end);

        return $query->where(function ($q) use ($startDate, $endDate) {
            // Non-recurring: check if date falls in range
            $q->where(function ($nonRecurring) use ($startDate, $endDate) {
                $nonRecurring->where('is_recurring', false)
                             ->whereBetween('date', [$startDate, $endDate]);
            })
            // Recurring: check if month/day falls in range for each year in the range
            ->orWhere(function ($recurring) use ($startDate, $endDate) {
                $recurring->where('is_recurring', true);

                // For recurring holidays, we need to check if they fall within the range
                // This is a simplified check - for dates spanning multiple years,
                // the service layer should handle the logic
                if ($startDate->year === $endDate->year) {
                    // Same year - check if month-day falls in range
                    $recurring->whereRaw('DATE_FORMAT(date, "%m-%d") >= ?', [$startDate->format('m-d')])
                              ->whereRaw('DATE_FORMAT(date, "%m-%d") <= ?', [$endDate->format('m-d')]);
                }
                // For cross-year ranges, return all recurring and filter in PHP
            });
        });
    }
}
