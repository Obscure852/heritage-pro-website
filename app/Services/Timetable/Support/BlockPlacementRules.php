<?php

namespace App\Services\Timetable\Support;

/**
 * Centralized validation helpers for multi-period block placement.
 */
final class BlockPlacementRules {
    /**
     * Compute all valid start periods for 2-period blocks.
     *
     * Valid starts reset at each break-delimited segment and align to
     * segment offsets 0,2,4... (e.g. 1,3 then 5,7).
     *
     * @param int $periodsPerDay
     * @param int[] $breakAfterPeriods
     * @return int[]
     */
    public static function computeValidDoubleStartPeriods(int $periodsPerDay, array $breakAfterPeriods): array {
        if ($periodsPerDay < 2) {
            return [];
        }

        $normalizedBreaks = [];
        foreach ($breakAfterPeriods as $afterPeriod) {
            $p = (int) $afterPeriod;
            if ($p >= 1 && $p < $periodsPerDay) {
                $normalizedBreaks[$p] = true;
            }
        }
        $breaks = array_keys($normalizedBreaks);
        sort($breaks);

        $segmentStarts = [1];
        foreach ($breaks as $breakAfter) {
            $nextStart = $breakAfter + 1;
            if ($nextStart <= $periodsPerDay) {
                $segmentStarts[] = $nextStart;
            }
        }

        $segmentStarts = array_values(array_unique($segmentStarts));
        sort($segmentStarts);

        $validStarts = [];
        $segmentCount = count($segmentStarts);
        for ($i = 0; $i < $segmentCount; $i++) {
            $segmentStart = (int) $segmentStarts[$i];
            $segmentEnd = $periodsPerDay;
            if ($i + 1 < $segmentCount) {
                $segmentEnd = (int) $segmentStarts[$i + 1] - 1;
            }

            // 2-period blocks require start+1 within the segment.
            for ($start = $segmentStart; $start + 1 <= $segmentEnd; $start += 2) {
                $validStarts[$start] = true;
            }
        }

        $starts = array_keys($validStarts);
        sort($starts);

        return $starts;
    }

    /**
     * Determine whether this placement violates double alignment.
     *
     * @param int $startPeriod
     * @param int $duration
     * @param array<int, bool|int> $validDoubleStartSet
     */
    public static function isMisalignedDoubleStart(int $startPeriod, int $duration, array $validDoubleStartSet): bool {
        if ($duration !== 2) {
            return false;
        }

        return !isset($validDoubleStartSet[$startPeriod]);
    }
}

