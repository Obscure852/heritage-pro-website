<?php

namespace Tests\Unit\Timetable;

use App\Services\Timetable\ConstraintValidationService;
use App\Services\Timetable\PeriodSettingsService;
use App\Services\Timetable\SlotManagementService;
use PHPUnit\Framework\TestCase;

class SlotManagementServiceTest extends TestCase {
    public function test_validate_block_placement_rejects_invalid_double_start(): void {
        $service = $this->makeService(
            periodsPerDay: 8,
            breaks: [['after_period' => 4, 'label' => 'Tea Break']]
        );

        $error = $service->validateBlockPlacement(2, 2);

        $this->assertNotNull($error);
        $this->assertStringContainsString('Double period must start at one of: 1, 3, 5, 7', (string) $error);
    }

    public function test_validate_block_placement_accepts_aligned_double(): void {
        $service = $this->makeService(
            periodsPerDay: 8,
            breaks: [['after_period' => 4, 'label' => 'Tea Break']]
        );

        $error = $service->validateBlockPlacement(3, 2);

        $this->assertNull($error);
    }

    public function test_validate_block_placement_allows_triple_when_bounds_and_breaks_are_valid(): void {
        $service = $this->makeService(
            periodsPerDay: 8,
            breaks: [['after_period' => 4, 'label' => 'Tea Break']]
        );

        $error = $service->validateBlockPlacement(5, 3);

        $this->assertNull($error);
    }

    private function makeService(int $periodsPerDay, array $breaks): SlotManagementService {
        $periodSettings = $this->createMock(PeriodSettingsService::class);
        $constraintValidation = $this->createMock(ConstraintValidationService::class);

        $periodDefinitions = [];
        for ($period = 1; $period <= $periodsPerDay; $period++) {
            $periodDefinitions[] = ['period' => $period];
        }

        $periodSettings->method('getPeriodDefinitions')->willReturn($periodDefinitions);
        $periodSettings->method('getBreakIntervals')->willReturn($breaks);

        return new SlotManagementService($periodSettings, $constraintValidation);
    }
}

