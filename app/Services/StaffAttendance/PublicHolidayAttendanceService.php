<?php

namespace App\Services\StaffAttendance;

use App\Models\Leave\PublicHoliday;
use App\Models\StaffAttendance\StaffAttendanceCode;
use App\Models\StaffAttendance\StaffAttendanceRecord;
use App\Models\User;
use App\Services\Leave\PublicHolidayService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service for creating attendance records on public holidays.
 *
 * Automatically creates 'H' (Holiday) attendance records for all active staff
 * on public holiday dates. Integrates with PublicHolidayService for holiday detection.
 */
class PublicHolidayAttendanceService {
    /**
     * @var PublicHolidayService
     */
    protected PublicHolidayService $publicHolidayService;

    /**
     * Create a new service instance.
     *
     * @param PublicHolidayService $publicHolidayService
     */
    public function __construct(PublicHolidayService $publicHolidayService){
        $this->publicHolidayService = $publicHolidayService;
    }

    /**
     * Sync public holiday attendance records for all active staff.
     *
     * Creates attendance records with 'H' code and 'holiday' status for all
     * active staff members on the given date if it's a public holiday.
     * Skips users who already have attendance records for that date.
     *
     * @param Carbon|null $date The date to sync (defaults to today)
     * @return array{created: int, skipped: int, holiday_name: string|null}
     */
    public function syncPublicHolidays(?Carbon $date = null): array{
        $date = $date ?? Carbon::today();

        $result = [
            'created' => 0,
            'skipped' => 0,
            'holiday_name' => null,
        ];

        // Check if this date is a public holiday
        if (!$this->publicHolidayService->isHoliday($date)) {
            return $result;
        }

        // Get the holiday name for the date
        $result['holiday_name'] = $this->getHolidayName($date);

        // Get the 'H' (Holiday) attendance code
        $holidayCode = StaffAttendanceCode::where('code', 'H')->first();

        if (!$holidayCode) {
            Log::warning('Holiday attendance code (H) not found', [
                'date' => $date->toDateString(),
            ]);
            return $result;
        }

        // Get all active staff (status = 'Current')
        $activeStaff = User::where('status', 'Current')->get();

        // Get user IDs that already have attendance records for this date
        $existingRecordUserIds = StaffAttendanceRecord::forDate($date)
            ->pluck('user_id')
            ->toArray();

        DB::transaction(function () use ($date, $holidayCode, $activeStaff, $existingRecordUserIds, &$result) {
            foreach ($activeStaff as $user) {
                // Skip if user already has an attendance record for this date
                if (in_array($user->id, $existingRecordUserIds)) {
                    $result['skipped']++;
                    continue;
                }

                // Create holiday attendance record
                StaffAttendanceRecord::create([
                    'user_id' => $user->id,
                    'date' => $date,
                    'status' => StaffAttendanceRecord::STATUS_HOLIDAY,
                    'attendance_code_id' => $holidayCode->id,
                    'entry_type' => StaffAttendanceRecord::ENTRY_SYSTEM,
                    'notes' => "Public holiday: {$result['holiday_name']}",
                ]);

                $result['created']++;
            }
        });

        Log::info('Public holiday attendance records synced', [
            'date' => $date->toDateString(),
            'holiday_name' => $result['holiday_name'],
            'created' => $result['created'],
            'skipped' => $result['skipped'],
        ]);

        return $result;
    }

    /**
     * Get the holiday name for a specific date.
     *
     * Checks both non-recurring and recurring holidays.
     *
     * @param Carbon $date
     * @return string|null
     */
    protected function getHolidayName(Carbon $date): ?string{
        // Check non-recurring holidays first (exact date match)
        $holiday = PublicHoliday::active()
            ->where('is_recurring', false)
            ->whereDate('date', $date)
            ->first();

        if ($holiday) {
            return $holiday->name;
        }

        // Check recurring holidays (month and day match)
        $holiday = PublicHoliday::active()
            ->where('is_recurring', true)
            ->whereMonth('date', $date->month)
            ->whereDay('date', $date->day)
            ->first();

        return $holiday?->name;
    }
}
