<?php

namespace App\Console\Commands;

use App\Models\GradeSubject;
use App\Models\OptionalSubject;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixMissingOptionalSubjects extends Command {
    protected $signature = 'fix:missing-optional-subjects';
    protected $description = 'Create missing F5 optional subject classes (Home Management & Single Science) after year rollover';

    // F4 Optional Subject IDs that were NOT rolled over
    private const HM_OLD_IDS = [26, 62, 83, 101, 104]; // Home Management
    private const SSA_OLD_IDS = [86, 90, 95, 97, 100]; // Single Science / SSA

    // Key IDs
    private const F5_GRADE_ID = 4;
    private const TO_TERM_ID = 4;
    private const FROM_TERM_ID = 3;
    private const HM_SUBJECT_ID = 33;
    private const SSA_GRADE_SUBJECT_ID = 126; // Already exists in F5
    private const HM_F4_GRADE_SUBJECT_ID = 124; // Source for copying fields

    public function handle(): int {
        $this->info('=== Fix Missing F5 Optional Subjects ===');
        $this->newLine();

        // Pre-flight checks
        if (!$this->preflight()) {
            return self::FAILURE;
        }

        // Get the rollover history ID for mapping data
        $rolloverHistoryId = DB::table('rollover_histories')
            ->where('from_term_id', self::FROM_TERM_ID)
            ->where('to_term_id', self::TO_TERM_ID)
            ->value('id');

        if (!$rolloverHistoryId) {
            $this->error('No rollover history found for term 3 → term 4.');
            return self::FAILURE;
        }

        $this->info("Rollover history ID: {$rolloverHistoryId}");

        // Get class rollover mapping (old klass_id → new klass_id)
        $klassMapping = DB::table('rollover_mapping_data')
            ->where('rollover_history_id', $rolloverHistoryId)
            ->where('table_name', 'Classes')
            ->pluck('new_id', 'old_id')
            ->toArray();

        $this->info("Loaded " . count($klassMapping) . " class mappings.");

        DB::transaction(function () use ($rolloverHistoryId, $klassMapping) {
            // Step 1: Create Home Management GradeSubject for F5
            $hmGradeSubjectId = $this->createHmGradeSubject();

            // Step 2 & 3: Create OptionalSubjects and student allocations
            $optionalSubjectMapping = [];

            $this->newLine();
            $this->info('--- Home Management Classes ---');
            foreach (self::HM_OLD_IDS as $oldId) {
                $this->createPromotedOptionalSubject($oldId, $hmGradeSubjectId, $klassMapping, $optionalSubjectMapping);
            }

            $this->newLine();
            $this->info('--- Single Science / SSA Classes ---');
            foreach (self::SSA_OLD_IDS as $oldId) {
                $this->createPromotedOptionalSubject($oldId, self::SSA_GRADE_SUBJECT_ID, $klassMapping, $optionalSubjectMapping);
            }

            // Step 4: Store rollover mapping data
            $this->newLine();
            $this->info('--- Storing Rollover Mapping Data ---');
            $mappingRecords = [];
            foreach ($optionalSubjectMapping as $oldId => $newId) {
                $mappingRecords[] = [
                    'rollover_history_id' => $rolloverHistoryId,
                    'table_name' => 'OptionalSubjects',
                    'old_id' => $oldId,
                    'new_id' => $newId,
                    'created_at' => now(),
                ];
            }
            DB::table('rollover_mapping_data')->insert($mappingRecords);
            $this->info("Inserted " . count($mappingRecords) . " mapping entries.");

            // Summary
            $this->newLine();
            $this->info('=== Summary ===');
            $totalOptionalSubjects = OptionalSubject::where('grade_id', self::F5_GRADE_ID)
                ->where('term_id', self::TO_TERM_ID)
                ->count();
            $this->info("Total F5 optional subjects now: {$totalOptionalSubjects}");

            foreach ($optionalSubjectMapping as $oldId => $newId) {
                $studentCount = DB::table('student_optional_subjects')
                    ->where('optional_subject_id', $newId)
                    ->count();
                $name = OptionalSubject::find($newId)->name;
                $this->info("  {$name} (ID {$newId}): {$studentCount} students");
            }
        });

        $this->newLine();
        $this->info('Fix completed successfully.');
        return self::SUCCESS;
    }

    private function preflight(): bool {
        // Check SSA GradeSubject exists
        $ssaGs = GradeSubject::find(self::SSA_GRADE_SUBJECT_ID);
        if (!$ssaGs) {
            $this->error('Single Science GradeSubject (ID ' . self::SSA_GRADE_SUBJECT_ID . ') not found.');
            return false;
        }
        $this->info("Single Science GradeSubject found: ID {$ssaGs->id}, grade_id={$ssaGs->grade_id}, subject_id={$ssaGs->subject_id}");

        // Check HM F4 GradeSubject exists (source for copying)
        $hmF4Gs = GradeSubject::find(self::HM_F4_GRADE_SUBJECT_ID);
        if (!$hmF4Gs) {
            $this->error('Home Management F4 GradeSubject (ID ' . self::HM_F4_GRADE_SUBJECT_ID . ') not found.');
            return false;
        }
        $this->info("Home Management F4 GradeSubject found: ID {$hmF4Gs->id}, subject_id={$hmF4Gs->subject_id}");

        // Check HM GradeSubject doesn't already exist in F5
        $existingHmGs = GradeSubject::where('grade_id', self::F5_GRADE_ID)
            ->where('subject_id', self::HM_SUBJECT_ID)
            ->where('term_id', self::TO_TERM_ID)
            ->first();
        if ($existingHmGs) {
            $this->warn("Home Management GradeSubject already exists in F5: ID {$existingHmGs->id} — will reuse it.");
        }

        // Check that the old optional subjects exist
        $allOldIds = array_merge(self::HM_OLD_IDS, self::SSA_OLD_IDS);
        $existingOld = OptionalSubject::whereIn('id', $allOldIds)->pluck('id')->toArray();
        $missing = array_diff($allOldIds, $existingOld);
        if (!empty($missing)) {
            $this->error('Missing old optional subjects: ' . implode(', ', $missing));
            return false;
        }
        $this->info("All 10 old F4 optional subjects verified.");

        return true;
    }

    private function createHmGradeSubject(): int {
        // Check if it already exists
        $existing = GradeSubject::where('grade_id', self::F5_GRADE_ID)
            ->where('subject_id', self::HM_SUBJECT_ID)
            ->where('term_id', self::TO_TERM_ID)
            ->first();

        if ($existing) {
            $this->info("Home Management GradeSubject already exists: ID {$existing->id}");
            return $existing->id;
        }

        // Copy from the F4 Home Management GradeSubject
        $source = GradeSubject::findOrFail(self::HM_F4_GRADE_SUBJECT_ID);

        $gs = GradeSubject::create([
            'grade_id' => self::F5_GRADE_ID,
            'subject_id' => self::HM_SUBJECT_ID,
            'term_id' => self::TO_TERM_ID,
            'department_id' => $source->department_id,
            'sequence' => $source->sequence,
            'year' => $source->year,
            'type' => $source->type,
            'mandatory' => $source->mandatory,
            'active' => 1,
        ]);

        $this->info("Created Home Management GradeSubject for F5: ID {$gs->id}");
        return $gs->id;
    }

    private function createPromotedOptionalSubject(int $oldId, int $gradeSubjectId, array $klassMapping, array &$optionalSubjectMapping): void {
        $old = OptionalSubject::with('students')->findOrFail($oldId);
        $promotedName = $this->promoteClassName($old->name);

        // Check if it already exists
        $existing = OptionalSubject::where('grade_subject_id', $gradeSubjectId)
            ->where('name', $promotedName)
            ->where('term_id', self::TO_TERM_ID)
            ->first();

        if ($existing) {
            $this->warn("  {$promotedName} already exists (ID {$existing->id}), skipping creation.");
            $optionalSubjectMapping[$oldId] = $existing->id;
            return;
        }

        // Create the new F5 optional subject
        $new = OptionalSubject::create([
            'name' => $promotedName,
            'grade_subject_id' => $gradeSubjectId,
            'user_id' => $old->user_id,
            'assistant_user_id' => $old->assistant_user_id,
            'venue_id' => $old->venue_id,
            'grouping' => $old->grouping,
            'grade_id' => self::F5_GRADE_ID,
            'term_id' => self::TO_TERM_ID,
            'active' => 1,
        ]);

        $optionalSubjectMapping[$oldId] = $new->id;
        $this->info("  Created: {$old->name} (ID {$oldId}) → {$promotedName} (ID {$new->id})");

        // Step 3: Create student allocations
        $studentRecords = [];
        $oldStudents = DB::table('student_optional_subjects')
            ->where('optional_subject_id', $oldId)
            ->where('term_id', self::FROM_TERM_ID)
            ->get();

        $skipped = 0;
        foreach ($oldStudents as $record) {
            $newKlassId = $klassMapping[$record->klass_id] ?? null;
            if (!$newKlassId) {
                $this->warn("    No class mapping for klass_id {$record->klass_id} (student {$record->student_id}), skipping.");
                $skipped++;
                continue;
            }

            $studentRecords[] = [
                'student_id' => $record->student_id,
                'optional_subject_id' => $new->id,
                'term_id' => self::TO_TERM_ID,
                'klass_id' => $newKlassId,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($studentRecords)) {
            DB::table('student_optional_subjects')->insert($studentRecords);
        }

        $this->info("    Allocated " . count($studentRecords) . " students" . ($skipped > 0 ? " ({$skipped} skipped)" : ""));
    }

    private function promoteClassName(string $name): string {
        // Replace the first digit with the next one up: "4ABCDE-HM" → "5ABCDE-HM"
        return preg_replace_callback('/^(\d)/', function ($matches) {
            return (string)((int)$matches[1] + 1);
        }, $name);
    }
}
