<?php

namespace Tests\Unit\Timetable;

use App\Services\Timetable\Support\BlockPlacementRules;
use PHPUnit\Framework\TestCase;

class BlockPlacementRulesTest extends TestCase {
    public function test_compute_valid_double_start_periods_with_single_break(): void {
        $starts = BlockPlacementRules::computeValidDoubleStartPeriods(8, [4]);

        $this->assertSame([1, 3, 5, 7], $starts);
    }

    public function test_compute_valid_double_start_periods_without_breaks(): void {
        $starts = BlockPlacementRules::computeValidDoubleStartPeriods(6, []);

        $this->assertSame([1, 3, 5], $starts);
    }

    public function test_compute_valid_double_start_periods_with_multiple_breaks(): void {
        $starts = BlockPlacementRules::computeValidDoubleStartPeriods(9, [3, 6]);

        $this->assertSame([1, 4, 7], $starts);
    }

    public function test_compute_valid_double_start_periods_excludes_unpaired_segment_tail(): void {
        $starts = BlockPlacementRules::computeValidDoubleStartPeriods(9, [4]);

        $this->assertSame([1, 3, 5, 7], $starts);
        $this->assertNotContains(9, $starts);
    }
}

