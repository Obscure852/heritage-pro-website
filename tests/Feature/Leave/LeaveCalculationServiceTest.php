<?php

namespace Tests\Feature\Leave;

use App\Models\Leave\LeaveSetting;
use App\Services\Leave\LeaveCalculationService;
use App\Services\Leave\PublicHolidayService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

/**
 * Test cases for LeaveCalculationService.
 *
 * Tests the core leave day calculation logic including:
 * - Weekday counting (excluding weekends)
 * - Public holiday exclusion
 * - Half-day calculations
 * - Configurable weekend days
 */
class LeaveCalculationServiceTest extends TestCase {
    use DatabaseTransactions;

    protected LeaveCalculationService $service;
    protected MockInterface $publicHolidayServiceMock;

    protected function setUp(): void {
        parent::setUp();

        // Seed default weekend days setting
        LeaveSetting::set('weekend_days', ['saturday', 'sunday']);

        // Create mock for PublicHolidayService
        $this->publicHolidayServiceMock = Mockery::mock(PublicHolidayService::class);

        // Default: no holidays unless test specifies otherwise
        $this->publicHolidayServiceMock
            ->shouldReceive('isHoliday')
            ->andReturn(false)
            ->byDefault();

        $this->publicHolidayServiceMock
            ->shouldReceive('countHolidaysBetween')
            ->andReturn(0)
            ->byDefault();

        // Bind mock to container
        $this->app->instance(PublicHolidayService::class, $this->publicHolidayServiceMock);

        // Resolve service from container
        $this->service = $this->app->make(LeaveCalculationService::class);
    }

    protected function tearDown(): void {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test 1: Mon-Fri (5 days, no holidays) returns 5.0
     */
    public function test_calculates_weekdays_only_for_standard_week(): void {
        // Arrange: Monday 2026-02-02 to Friday 2026-02-06
        $startDate = Carbon::parse('2026-02-02'); // Monday
        $endDate = Carbon::parse('2026-02-06');   // Friday

        // Act
        $result = $this->service->calculateLeaveDays($startDate, $endDate);

        // Assert
        $this->assertEquals(5.0, $result);
    }

    /**
     * Test 2: Mon-Sun (7 days, no holidays) returns 5.0 (weekends excluded)
     */
    public function test_excludes_weekends_from_calculation(): void {
        // Arrange: Monday 2026-02-02 to Sunday 2026-02-08
        $startDate = Carbon::parse('2026-02-02'); // Monday
        $endDate = Carbon::parse('2026-02-08');   // Sunday

        // Act
        $result = $this->service->calculateLeaveDays($startDate, $endDate);

        // Assert: Only Mon-Fri counted = 5 days
        $this->assertEquals(5.0, $result);
    }

    /**
     * Test 3: Mon-Fri with 1 holiday on Wed returns 4.0
     */
    public function test_excludes_public_holidays(): void {
        // Arrange: Monday 2026-02-02 to Friday 2026-02-06
        $startDate = Carbon::parse('2026-02-02'); // Monday
        $endDate = Carbon::parse('2026-02-06');   // Friday
        $holidayDate = Carbon::parse('2026-02-04'); // Wednesday

        // Configure mock: Wednesday is a holiday
        $this->publicHolidayServiceMock
            ->shouldReceive('isHoliday')
            ->with(Mockery::on(function ($date) use ($holidayDate) {
                return $date->format('Y-m-d') === $holidayDate->format('Y-m-d');
            }))
            ->andReturn(true);

        // Act
        $result = $this->service->calculateLeaveDays($startDate, $endDate);

        // Assert: 5 weekdays - 1 holiday = 4 days
        $this->assertEquals(4.0, $result);
    }

    /**
     * Test 4: Monday AM only returns 0.5
     */
    public function test_half_day_start_calculates_correctly(): void {
        // Arrange: Monday 2026-02-02 (AM only)
        $startDate = Carbon::parse('2026-02-02'); // Monday
        $endDate = Carbon::parse('2026-02-02');   // Same day

        // Act: Start half-day AM means only morning
        $result = $this->service->calculateLeaveDays($startDate, $endDate, 'am', null);

        // Assert
        $this->assertEquals(0.5, $result);
    }

    /**
     * Test 5: Friday PM only returns 0.5
     */
    public function test_half_day_end_calculates_correctly(): void {
        // Arrange: Friday 2026-02-06 (PM only)
        $startDate = Carbon::parse('2026-02-06'); // Friday
        $endDate = Carbon::parse('2026-02-06');   // Same day

        // Act: End half-day PM means only afternoon
        $result = $this->service->calculateLeaveDays($startDate, $endDate, null, 'pm');

        // Assert
        $this->assertEquals(0.5, $result);
    }

    /**
     * Test 6: Mon AM to Tue PM returns 1.5
     * Monday: 0.5 (AM only) + Tuesday: 1.0 (full day, end on PM doesn't reduce)
     * Actually: Mon AM = 0.5, Tue full = 1.0 -> total 1.5
     */
    public function test_half_day_range_calculates_correctly(): void {
        // Arrange: Monday 2026-02-02 AM to Tuesday 2026-02-03 PM
        $startDate = Carbon::parse('2026-02-02'); // Monday
        $endDate = Carbon::parse('2026-02-03');   // Tuesday

        // Act: Start with AM half-day, end with PM half-day
        $result = $this->service->calculateLeaveDays($startDate, $endDate, 'am', 'pm');

        // Assert: Mon 0.5 (AM) + Tue 0.5 (PM) = 1.0
        // Wait - need to clarify the business logic:
        // Mon AM start = 0.5 for Monday
        // Tue PM end = 0.5 for Tuesday
        // Total = 1.0
        // But if AM start means "start from AM onwards" = full day
        // And PM end means "end at PM" = full day
        // Then Mon AM to Tue PM = 2.0
        //
        // Clarification: start_half_day 'am' means take ONLY the AM portion of start day
        // end_half_day 'pm' means take ONLY the PM portion of end day
        // So: Mon AM (0.5) + Tue PM (0.5) = 1.0
        $this->assertEquals(1.0, $result);
    }

    /**
     * Test 7: Same day with both AM and PM (full day) returns 1.0
     */
    public function test_same_day_both_halves_returns_one(): void {
        // Arrange: Monday 2026-02-02 (both halves)
        $startDate = Carbon::parse('2026-02-02'); // Monday
        $endDate = Carbon::parse('2026-02-02');   // Same day

        // Act: Both AM and PM specified on same day = full day
        $result = $this->service->calculateLeaveDays($startDate, $endDate, 'am', 'pm');

        // Assert
        $this->assertEquals(1.0, $result);
    }

    /**
     * Test 8: Weekend + holiday overlap counts as 1 exclusion (no double counting)
     * If a holiday falls on a weekend, it should not be double-deducted.
     */
    public function test_does_not_double_count_holiday_on_weekend(): void {
        // Arrange: Monday 2026-02-02 to Sunday 2026-02-08
        // Saturday 2026-02-07 is both a weekend AND a holiday
        $startDate = Carbon::parse('2026-02-02'); // Monday
        $endDate = Carbon::parse('2026-02-08');   // Sunday

        $saturdayHoliday = Carbon::parse('2026-02-07'); // Saturday

        // Configure mock: Saturday is a holiday
        $this->publicHolidayServiceMock
            ->shouldReceive('isHoliday')
            ->with(Mockery::on(function ($date) use ($saturdayHoliday) {
                return $date->format('Y-m-d') === $saturdayHoliday->format('Y-m-d');
            }))
            ->andReturn(true);

        // Act
        $result = $this->service->calculateLeaveDays($startDate, $endDate);

        // Assert: Still 5 working days (Mon-Fri), weekend already excluded
        // The holiday on Saturday shouldn't reduce further
        $this->assertEquals(5.0, $result);
    }

    /**
     * Test 9: Sat-Sun returns 0.0 (weekend-only range)
     */
    public function test_returns_zero_for_weekend_only_range(): void {
        // Arrange: Saturday 2026-02-07 to Sunday 2026-02-08
        $startDate = Carbon::parse('2026-02-07'); // Saturday
        $endDate = Carbon::parse('2026-02-08');   // Sunday

        // Act
        $result = $this->service->calculateLeaveDays($startDate, $endDate);

        // Assert
        $this->assertEquals(0.0, $result);
    }

    /**
     * Test 10: Uses configurable weekend days from LeaveSetting.
     * Some countries have Friday-Saturday as weekend.
     */
    public function test_uses_configurable_weekend_days(): void {
        // Arrange: Change weekend to Friday-Saturday (Middle East pattern)
        LeaveSetting::set('weekend_days', ['friday', 'saturday']);

        // Need to recreate service to pick up new setting
        $this->service = $this->app->make(LeaveCalculationService::class);

        // Test a week: Sunday 2026-02-01 to Saturday 2026-02-07
        $startDate = Carbon::parse('2026-02-01'); // Sunday (now a workday)
        $endDate = Carbon::parse('2026-02-07');   // Saturday (weekend)

        // Act
        $result = $this->service->calculateLeaveDays($startDate, $endDate);

        // Assert: Sun, Mon, Tue, Wed, Thu = 5 working days
        // Fri, Sat = weekend (excluded)
        $this->assertEquals(5.0, $result);
    }

    /**
     * Test 11: Helper method isWeekend returns correct boolean.
     */
    public function test_is_weekend_returns_correct_value(): void {
        // Arrange
        $monday = Carbon::parse('2026-02-02');    // Monday
        $saturday = Carbon::parse('2026-02-07'); // Saturday
        $sunday = Carbon::parse('2026-02-08');   // Sunday

        // Act & Assert
        $this->assertFalse($this->service->isWeekend($monday));
        $this->assertTrue($this->service->isWeekend($saturday));
        $this->assertTrue($this->service->isWeekend($sunday));
    }

    /**
     * Test 12: Helper method countWeekends returns correct count.
     */
    public function test_count_weekends_returns_correct_count(): void {
        // Arrange: Mon 2026-02-02 to Sun 2026-02-15 (2 full weeks)
        $startDate = Carbon::parse('2026-02-02');
        $endDate = Carbon::parse('2026-02-15');

        // Act
        $result = $this->service->countWeekends($startDate, $endDate);

        // Assert: 2 Saturdays + 2 Sundays = 4 weekend days
        $this->assertEquals(4, $result);
    }

    /**
     * Test 13: Helper method isWorkingDay combines weekend and holiday check.
     */
    public function test_is_working_day_returns_correct_value(): void {
        // Arrange
        $monday = Carbon::parse('2026-02-02');    // Monday (working day)
        $saturday = Carbon::parse('2026-02-07'); // Saturday (weekend)
        $holiday = Carbon::parse('2026-02-04');  // Wednesday (will be a holiday)

        // Configure mock: Wednesday is a holiday
        $this->publicHolidayServiceMock
            ->shouldReceive('isHoliday')
            ->with(Mockery::on(function ($date) use ($holiday) {
                return $date->format('Y-m-d') === $holiday->format('Y-m-d');
            }))
            ->andReturn(true);

        // Act & Assert
        $this->assertTrue($this->service->isWorkingDay($monday));
        $this->assertFalse($this->service->isWorkingDay($saturday));
        $this->assertFalse($this->service->isWorkingDay($holiday));
    }
}
