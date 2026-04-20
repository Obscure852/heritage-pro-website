<?php

namespace App\Services\Timetable;

use App\Models\Timetable\Timetable;
use App\Models\Timetable\TimetableAuditLog;
use App\Models\Timetable\TimetableBlockAllocation;
use App\Models\Timetable\TimetableSetting;
use App\Models\Timetable\TimetableSlot;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class PeriodSettingsService {
    /**
     * Get period definitions (bell schedule).
     *
     * @return array Array of period definition objects
     */
    public function getPeriodDefinitions(): array {
        return TimetableSetting::get('period_definitions', []);
    }

    /**
     * Save period definitions and auto-update periods_per_day.
     * Recalculates all times from first period's start_time forward,
     * chaining durations and inserting breaks.
     *
     * @param array $periods Array of [period, start_time, duration] (start_time only used for period 1)
     * @param array $breaks Array of [after_period, duration, label]
     * @param int|null $userId
     */
    public function savePeriodDefinitions(array $periods, array $breaks, ?int $userId = null): void {
        // Index breaks by after_period for quick lookup
        $breaksByAfter = [];
        foreach ($breaks as $break) {
            $breaksByAfter[$break['after_period']] = $break;
        }

        // Sort periods by period number
        usort($periods, fn($a, $b) => $a['period'] <=> $b['period']);

        // Recalculate all times from Period 1's start_time forward
        $currentTime = Carbon::createFromFormat('H:i', $periods[0]['start_time']);
        $recalculated = [];
        $recalculatedBreaks = [];

        foreach ($periods as $index => $period) {
            $startTime = $currentTime->format('H:i');
            $duration = (int) $period['duration'];
            $endTime = $currentTime->copy()->addMinutes($duration)->format('H:i');

            $recalculated[] = [
                'period' => $period['period'],
                'start_time' => $startTime,
                'end_time' => $endTime,
                'duration' => $duration,
            ];

            $currentTime->addMinutes($duration);

            // Check if a break follows this period
            if (isset($breaksByAfter[$period['period']])) {
                $break = $breaksByAfter[$period['period']];
                $breakStart = $currentTime->format('H:i');
                $breakDuration = (int) $break['duration'];
                $breakEnd = $currentTime->copy()->addMinutes($breakDuration)->format('H:i');

                $recalculatedBreaks[] = [
                    'after_period' => $break['after_period'],
                    'duration' => $breakDuration,
                    'label' => $break['label'],
                    'start_time' => $breakStart,
                    'end_time' => $breakEnd,
                ];

                $currentTime->addMinutes($breakDuration);
            }
        }

        // Save period definitions
        TimetableSetting::set('period_definitions', $recalculated, $userId);

        // Update periods_per_day to match count
        TimetableSetting::set('periods_per_day', count($recalculated), $userId);

        // Save break intervals with computed times
        TimetableSetting::set('break_intervals', $recalculatedBreaks, $userId);
    }

    /**
     * Get break intervals.
     *
     * @return array
     */
    public function getBreakIntervals(): array {
        return TimetableSetting::get('break_intervals', []);
    }

    /**
     * Save break intervals with computed start/end times.
     *
     * @param array $breaks
     * @param int|null $userId
     */
    public function saveBreakIntervals(array $breaks, ?int $userId = null): void {
        // Recalculate break times based on current period definitions
        $periods = $this->getPeriodDefinitions();
        $periodsByNumber = [];
        foreach ($periods as $period) {
            $periodsByNumber[$period['period']] = $period;
        }

        $recalculated = [];
        foreach ($breaks as $break) {
            $afterPeriod = $break['after_period'];
            if (isset($periodsByNumber[$afterPeriod])) {
                $breakStart = $periodsByNumber[$afterPeriod]['end_time'];
                $breakEnd = Carbon::createFromFormat('H:i', $breakStart)
                    ->addMinutes((int) $break['duration'])
                    ->format('H:i');

                $recalculated[] = [
                    'after_period' => $afterPeriod,
                    'duration' => (int) $break['duration'],
                    'label' => $break['label'],
                    'start_time' => $breakStart,
                    'end_time' => $breakEnd,
                ];
            }
        }

        TimetableSetting::set('break_intervals', $recalculated, $userId);
    }

    /**
     * Find existing multi-period blocks that would span across the given breaks.
     *
     * @param array $breaks Array of break definitions with after_period
     * @return array<int, array<string, mixed>>
     */
    public function findBlocksSpanningBreaks(array $breaks): array {
        $breakSet = [];
        foreach ($breaks as $break) {
            $after = (int) ($break['after_period'] ?? 0);
            if ($after > 0) {
                $breakSet[$after] = true;
            }
        }

        if (empty($breakSet)) {
            return [];
        }

        $blockGroups = TimetableSlot::whereNotNull('block_id')
            ->where('duration', '>', 1)
            ->with([
                'klassSubject.klass',
                'klassSubject.gradeSubject.subject',
                'optionalSubject.gradeSubject.subject',
            ])
            ->orderBy('day_of_cycle')
            ->orderBy('period_number')
            ->get()
            ->groupBy('block_id');

        $conflicts = [];
        foreach ($blockGroups as $blockId => $slots) {
            $ordered = $slots->sortBy('period_number')->values();
            $first = $ordered->first();
            if (!$first) {
                continue;
            }

            $duration = max((int) $first->duration, $ordered->count());
            if ($duration <= 1) {
                continue;
            }

            $start = (int) $first->period_number;
            $end = $start + $duration - 1;

            for ($p = $start; $p < $end; $p++) {
                if (!isset($breakSet[$p])) {
                    continue;
                }

                $subjectName = $first->klassSubject?->gradeSubject?->subject?->name
                    ?? $first->optionalSubject?->gradeSubject?->subject?->name
                    ?? $first->optionalSubject?->name
                    ?? 'Unknown subject';

                $klassName = $first->klassSubject?->klass?->name ?? 'Unknown class';

                $conflicts[] = [
                    'block_id' => $blockId,
                    'timetable_id' => (int) $first->timetable_id,
                    'day_of_cycle' => (int) $first->day_of_cycle,
                    'start_period' => $start,
                    'end_period' => $end,
                    'break_after_period' => $p,
                    'klass_name' => $klassName,
                    'subject_name' => $subjectName,
                ];

                // One break conflict is enough per block for user feedback.
                break;
            }
        }

        return $conflicts;
    }

    /**
     * Get merged day schedule -- periods and breaks in chronological order.
     * Used by visual preview and timetable grid rendering.
     * Returns array of items with type ('period' or 'break'), start_time, end_time, etc.
     *
     * @return array
     */
    public function getDaySchedule(): array {
        $periods = $this->getPeriodDefinitions();
        $breaks = $this->getBreakIntervals();

        // Index breaks by after_period for quick lookup
        $breaksByAfter = [];
        foreach ($breaks as $break) {
            $breaksByAfter[$break['after_period']] = $break;
        }

        $schedule = [];
        foreach ($periods as $period) {
            $schedule[] = [
                'type' => 'period',
                'period' => $period['period'],
                'start_time' => $period['start_time'],
                'end_time' => $period['end_time'],
                'duration' => $period['duration'],
            ];

            // Insert break after this period if one exists
            if (isset($breaksByAfter[$period['period']])) {
                $break = $breaksByAfter[$period['period']];
                $schedule[] = [
                    'type' => 'break',
                    'label' => $break['label'],
                    'start_time' => $break['start_time'],
                    'end_time' => $break['end_time'],
                    'duration' => $break['duration'],
                ];
            }
        }

        return $schedule;
    }

    /**
     * Get block allocations for a timetable, optionally filtered by klass_id.
     * Eager loads klassSubject with teacher, klass, gradeSubject.subject relationships.
     *
     * @param int $timetableId
     * @param int|null $klassId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getBlockAllocations(int $timetableId, ?int $klassId = null): Collection {
        $query = TimetableBlockAllocation::where('timetable_id', $timetableId)
            ->with(['klassSubject.teacher', 'klassSubject.klass', 'klassSubject.gradeSubject.subject']);

        if ($klassId !== null) {
            $query->whereHas('klassSubject', function ($q) use ($klassId) {
                $q->where('klass_id', $klassId);
            });
        }

        return $query->get();
    }

    /**
     * Save block allocations for a timetable.
     * Uses upsert pattern: for each allocation, updateOrCreate by timetable_id + klass_subject_id.
     * Wraps in DB::transaction.
     *
     * @param int $timetableId
     * @param array $allocations Array of [klass_subject_id, singles, doubles, triples]
     * @param int|null $userId For audit logging
     */
    public function saveBlockAllocations(int $timetableId, array $allocations, ?int $userId = null): void {
        DB::transaction(function () use ($timetableId, $allocations, $userId) {
            foreach ($allocations as $alloc) {
                TimetableBlockAllocation::updateOrCreate(
                    [
                        'timetable_id' => $timetableId,
                        'klass_subject_id' => $alloc['klass_subject_id'],
                    ],
                    [
                        'singles' => $alloc['singles'] ?? 0,
                        'doubles' => $alloc['doubles'] ?? 0,
                        'triples' => $alloc['triples'] ?? 0,
                    ]
                );
            }

            $timetable = Timetable::findOrFail($timetableId);
            TimetableAuditLog::log(
                $timetable,
                'block_allocations_updated',
                'Block allocations updated for ' . count($allocations) . ' class-subject(s)'
            );
        });
    }

    /**
     * Get optional coupling groups from timetable_settings.
     *
     * @return array
     */
    public function getCouplingGroups(): array {
        return TimetableSetting::get('optional_coupling_groups', []);
    }

    /**
     * Save optional coupling groups to timetable_settings.
     *
     * @param array $groups
     * @param int|null $userId
     */
    public function saveCouplingGroups(array $groups, ?int $userId = null): void {
        TimetableSetting::set('optional_coupling_groups', $groups, $userId);
    }

    /**
     * Validate that total block allocations for a class don't exceed available slots.
     * Available slots = periods_per_day * 6 (cycle_days is hardcoded at 6 per D3).
     *
     * @param int $timetableId
     * @param int $klassId
     * @return array ['available' => int, 'allocated' => int, 'exceeded' => bool]
     */
    public function validateClassAllocation(int $timetableId, int $klassId): array {
        $periodsPerDay = (int) TimetableSetting::get('periods_per_day', 7);
        $available = $periodsPerDay * 6;

        $allocated = (int) TimetableBlockAllocation::where('timetable_id', $timetableId)
            ->whereHas('klassSubject', function ($q) use ($klassId) {
                $q->where('klass_id', $klassId);
            })
            ->sum('total_periods');

        return [
            'available' => $available,
            'allocated' => $allocated,
            'exceeded' => $allocated > $available,
        ];
    }
}
