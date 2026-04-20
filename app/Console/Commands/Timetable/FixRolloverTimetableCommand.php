<?php

namespace App\Console\Commands\Timetable;

use App\Models\RolloverHistory;
use App\Models\Term;
use App\Models\Timetable\Timetable;
use App\Models\Timetable\TimetableBlockAllocation;
use App\Models\Timetable\TimetableSetting;
use App\Models\Timetable\TimetableSlot;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixRolloverTimetableCommand extends Command {
    protected $signature = 'timetable:fix-rollover {--dry-run : Preview changes without applying}';
    protected $description = 'Remap stale KlassSubject, OptionalSubject, and Grade IDs in timetable data after a year rollover';

    public function handle(): int {
        $dryRun = $this->option('dry-run');

        // Step 1: Auto-detect active term, timetables, and rollover history
        $activeTerm = Term::currentOrLastActiveTerm();
        if (!$activeTerm) {
            $this->error('No active term found.');
            return Command::FAILURE;
        }

        $this->info("Active term: {$activeTerm->year} Term {$activeTerm->term} (ID: {$activeTerm->id})");

        $timetables = Timetable::forTerm($activeTerm->id)->get();
        if ($timetables->isEmpty()) {
            $this->error('No timetables found for the current term.');
            return Command::FAILURE;
        }

        $this->info("Found {$timetables->count()} timetable(s) for this term.");

        $rollover = RolloverHistory::where('to_term_id', $activeTerm->id)
            ->where('status', RolloverHistory::STATUS_COMPLETED)
            ->latest('rollover_timestamp')
            ->first();

        if (!$rollover) {
            $this->error('No completed rollover found for the current term.');
            return Command::FAILURE;
        }

        $this->info("Using rollover #{$rollover->id} (from term {$rollover->from_term_id} -> {$rollover->to_term_id})");

        // Step 2: Load ID mappings from rollover_mapping_data
        $mappings = DB::table('rollover_mapping_data')
            ->where('rollover_history_id', $rollover->id)
            ->get()
            ->groupBy('table_name');

        $ksMap = $this->buildMap($mappings, 'KlassSubjects');
        $osMap = $this->buildMap($mappings, 'OptionalSubjects');
        $gradeMap = $this->buildMap($mappings, 'Grades');

        // If no KlassSubjects mapping stored, derive it from Classes + GradeSubjects
        if (empty($ksMap)) {
            $this->warn('No KlassSubjects mapping found in rollover data — deriving from Classes + GradeSubjects...');
            $ksMap = $this->deriveKlassSubjectMapping($rollover, $mappings);
            $this->info("Derived " . count($ksMap) . " KlassSubject mapping(s).");
        }

        $this->info("Mappings loaded — KlassSubjects: " . count($ksMap) . ", OptionalSubjects: " . count($osMap) . ", Grades: " . count($gradeMap));

        // Counters
        $stats = [
            'slots_ks_updated' => 0,
            'slots_os_updated' => 0,
            'slots_cg_updated' => 0,
            'alloc_ks_updated' => 0,
            'alloc_ks_deleted' => 0,
            'coupling_groups_updated' => 0,
            'orphaned_ks' => [],
            'orphaned_os' => [],
        ];

        $timetableIds = $timetables->pluck('id')->toArray();

        // Collect current-term klass_subject IDs to detect stale refs
        $currentTermKsIds = DB::table('klass_subject')
            ->where('term_id', $activeTerm->id)
            ->whereNull('deleted_at')
            ->pluck('id')
            ->flip()
            ->toArray();

        $callback = function () use ($timetableIds, $ksMap, $osMap, $gradeMap, $currentTermKsIds, &$stats) {
            // Step 3: Remap timetable_slots
            $slots = TimetableSlot::whereIn('timetable_id', $timetableIds)->get();

            foreach ($slots as $slot) {
                $changed = false;

                // 3a: klass_subject_id
                if ($slot->klass_subject_id !== null) {
                    $oldId = $slot->klass_subject_id;
                    if (isset($ksMap[$oldId])) {
                        $slot->klass_subject_id = $ksMap[$oldId];
                        $changed = true;
                        $stats['slots_ks_updated']++;
                    } elseif (!DB::table('klass_subject')->where('id', $oldId)->exists()) {
                        $stats['orphaned_ks'][] = "slot:{$slot->id} ks:{$oldId}";
                    }
                }

                // 3b: optional_subject_id
                if ($slot->optional_subject_id !== null) {
                    $oldId = $slot->optional_subject_id;
                    if (isset($osMap[$oldId])) {
                        $slot->optional_subject_id = $osMap[$oldId];
                        $changed = true;
                        $stats['slots_os_updated']++;
                    } elseif (!DB::table('optional_subjects')->where('id', $oldId)->exists()) {
                        $stats['orphaned_os'][] = "slot:{$slot->id} os:{$oldId}";
                    }
                }

                // 3c: coupling_group_key
                if ($slot->coupling_group_key !== null) {
                    $newKey = $this->remapCouplingGroupKey($slot->coupling_group_key, $gradeMap);
                    if ($newKey !== $slot->coupling_group_key) {
                        $slot->coupling_group_key = $newKey;
                        $changed = true;
                        $stats['slots_cg_updated']++;
                    }
                }

                if ($changed) {
                    $slot->save();
                }
            }

            // Step 4: Remap or remove stale timetable_block_allocations
            $allocations = TimetableBlockAllocation::whereIn('timetable_id', $timetableIds)->get();

            // Index existing allocations by (timetable_id, klass_subject_id) to detect duplicates
            $existingAllocKeys = $allocations
                ->keyBy(fn($a) => $a->timetable_id . ':' . $a->klass_subject_id);

            foreach ($allocations as $alloc) {
                $oldId = $alloc->klass_subject_id;

                // Skip allocations already pointing to current-term klass_subjects
                if (isset($currentTermKsIds[$oldId])) {
                    continue;
                }

                if (isset($ksMap[$oldId])) {
                    $newId = $ksMap[$oldId];
                    $targetKey = $alloc->timetable_id . ':' . $newId;

                    if (isset($existingAllocKeys[$targetKey])) {
                        // A correct allocation already exists for the new ID — delete the stale one
                        $alloc->delete();
                        $stats['alloc_ks_deleted']++;
                    } else {
                        // No existing allocation for the new ID — remap this one
                        $alloc->klass_subject_id = $newId;
                        $alloc->save();
                        $stats['alloc_ks_updated']++;
                    }
                } else {
                    // Stale allocation with no mapping (e.g. alumni/graduated class) — remove it
                    $alloc->delete();
                    $stats['alloc_ks_deleted']++;
                }
            }

            // Step 5: Remap coupling groups in TimetableSetting
            $groups = TimetableSetting::get('optional_coupling_groups', []);
            if (!empty($groups)) {
                $updatedGroups = [];
                foreach ($groups as $group) {
                    $oldGradeId = (int) ($group['grade_id'] ?? 0);
                    $newGradeId = $gradeMap[$oldGradeId] ?? $oldGradeId;

                    $newOsIds = [];
                    foreach ($group['optional_subject_ids'] ?? [] as $oldOsId) {
                        $newOsId = $osMap[(int) $oldOsId] ?? null;
                        if ($newOsId !== null) {
                            $newOsIds[] = $newOsId;
                        }
                    }

                    $changed = ($newGradeId !== $oldGradeId) || ($newOsIds !== array_map('intval', $group['optional_subject_ids'] ?? []));

                    if ($changed) {
                        $stats['coupling_groups_updated']++;
                    }

                    $updatedGroups[] = [
                        'grade_id' => $newGradeId,
                        'label' => $group['label'],
                        'singles' => $group['singles'],
                        'doubles' => $group['doubles'],
                        'triples' => $group['triples'],
                        'optional_subject_ids' => $newOsIds,
                    ];
                }

                TimetableSetting::set('optional_coupling_groups', $updatedGroups);
            }
        };

        // Execute within transaction (rolled back for dry-run)
        if ($dryRun) {
            DB::beginTransaction();
            try {
                $callback();
            } finally {
                DB::rollBack();
            }
            $this->warn('DRY RUN — no changes were applied.');
        } else {
            DB::transaction($callback);
            $this->info('Changes applied successfully.');
        }

        // Step 6: Report results
        $this->newLine();
        $this->table(
            ['Metric', 'Count'],
            [
                ['Slots: klass_subject_id remapped', $stats['slots_ks_updated']],
                ['Slots: optional_subject_id remapped', $stats['slots_os_updated']],
                ['Slots: coupling_group_key remapped', $stats['slots_cg_updated']],
                ['Block allocations: klass_subject_id remapped', $stats['alloc_ks_updated']],
                ['Block allocations: stale duplicates removed', $stats['alloc_ks_deleted']],
                ['Coupling groups remapped', $stats['coupling_groups_updated']],
                ['Orphaned klass_subject refs', count($stats['orphaned_ks'])],
                ['Orphaned optional_subject refs', count($stats['orphaned_os'])],
            ]
        );

        if (!empty($stats['orphaned_ks'])) {
            $this->warn('Orphaned klass_subject slots (old ID not in mapping and record missing):');
            foreach ($stats['orphaned_ks'] as $ref) {
                $this->line("  - {$ref}");
            }
        }

        if (!empty($stats['orphaned_os'])) {
            $this->warn('Orphaned optional_subject slots (old ID not in mapping and record missing):');
            foreach ($stats['orphaned_os'] as $ref) {
                $this->line("  - {$ref}");
            }
        }

        Log::info('timetable:fix-rollover completed', [
            'dry_run' => $dryRun,
            'rollover_id' => $rollover->id,
            'timetable_ids' => $timetableIds,
            'stats' => array_merge($stats, [
                'orphaned_ks' => count($stats['orphaned_ks']),
                'orphaned_os' => count($stats['orphaned_os']),
            ]),
        ]);

        return Command::SUCCESS;
    }

    /**
     * Build an old_id => new_id map from grouped rollover_mapping_data.
     */
    private function buildMap($mappings, string $tableName): array {
        if (!isset($mappings[$tableName])) {
            return [];
        }

        $map = [];
        foreach ($mappings[$tableName] as $row) {
            $map[(int) $row->old_id] = (int) $row->new_id;
        }
        return $map;
    }

    /**
     * Derive KlassSubject old->new mapping using Classes mapping + subject_id matching.
     *
     * The Classes mapping follows grade promotion (e.g. 2A in F2 -> 3A in F3),
     * so we match by new_klass_id + subject_id within the new klass's grade.
     */
    private function deriveKlassSubjectMapping($rollover, $mappings): array {
        $classMap = $this->buildMap($mappings, 'Classes');

        if (empty($classMap)) {
            $this->warn('Cannot derive KlassSubject mapping — Classes mapping is empty.');
            return [];
        }

        // Load old grade_subjects to resolve subject_id from old grade_subject_id
        $oldGradeSubjects = DB::table('grade_subject')
            ->where('term_id', $rollover->from_term_id)
            ->get()
            ->keyBy('id');

        $oldKlassSubjects = DB::table('klass_subject')
            ->where('term_id', $rollover->from_term_id)
            ->get();

        // Index new klass_subjects by (klass_id, subject_id) for lookup.
        // Join with grade_subject to get the subject_id.
        $newKlassSubjects = DB::table('klass_subject')
            ->join('grade_subject', 'klass_subject.grade_subject_id', '=', 'grade_subject.id')
            ->where('klass_subject.term_id', $rollover->to_term_id)
            ->select('klass_subject.id', 'klass_subject.klass_id', 'grade_subject.subject_id')
            ->get()
            ->keyBy(fn($ks) => $ks->klass_id . ':' . $ks->subject_id);

        $map = [];
        foreach ($oldKlassSubjects as $old) {
            $newKlassId = $classMap[$old->klass_id] ?? null;
            if ($newKlassId === null) {
                continue;
            }

            $oldGs = $oldGradeSubjects[$old->grade_subject_id] ?? null;
            if (!$oldGs) {
                continue;
            }

            $key = $newKlassId . ':' . $oldGs->subject_id;
            if (isset($newKlassSubjects[$key])) {
                $map[(int) $old->id] = (int) $newKlassSubjects[$key]->id;
            }
        }

        return $map;
    }

    /**
     * Remap a coupling_group_key by replacing the grade ID component.
     *
     * Format: cg_{gradeId}_{label}_{s|d|t}{index}
     */
    private function remapCouplingGroupKey(string $key, array $gradeMap): string {
        if (!preg_match('/^cg_(\d+)_(.+)_([sdt]\d+)$/', $key, $matches)) {
            return $key;
        }

        $oldGradeId = (int) $matches[1];
        $label = $matches[2];
        $suffix = $matches[3];

        if (!isset($gradeMap[$oldGradeId])) {
            return $key;
        }

        return "cg_{$gradeMap[$oldGradeId]}_{$label}_{$suffix}";
    }
}
