<?php

namespace App\Services\Timetable;

use App\Models\Timetable\Timetable;
use App\Models\Timetable\TimetableAuditLog;
use App\Models\Timetable\TimetableSlot;
use App\Models\Timetable\TimetableVersion;
use Illuminate\Support\Facades\DB;

class TimetablePublishingService {
    // Allowed status transitions
    const TRANSITIONS = [
        'draft'     => ['published'],
        'published' => ['draft', 'archived'],
        'archived'  => ['draft'],
    ];

    /**
     * Publish a timetable: archive any existing published timetable for the same
     * term, create a version snapshot, update status to published.
     *
     * @throws \InvalidArgumentException if transition is not allowed
     */
    public function publish(Timetable $timetable, int $userId): Timetable {
        return DB::transaction(function () use ($timetable, $userId) {
            $timetable = Timetable::lockForUpdate()->findOrFail($timetable->id);

            if (!$this->isValidTransition($timetable->status, Timetable::STATUS_PUBLISHED)) {
                throw new \InvalidArgumentException(
                    "Cannot publish: timetable is currently '{$timetable->status}'."
                );
            }

            // Archive the currently published timetable for this term (if any)
            $this->archiveCurrentPublished($timetable->term_id, $timetable->id, $userId);

            // Create version snapshot BEFORE changing status
            $this->createVersionSnapshot($timetable, $userId);

            // Update status to published
            $timetable->update([
                'status' => Timetable::STATUS_PUBLISHED,
                'published_at' => now(),
                'published_by' => $userId,
            ]);

            TimetableAuditLog::log($timetable, 'published', 'Timetable published');

            return $timetable->refresh();
        });
    }

    /**
     * Unpublish a timetable: revert to draft status for further editing.
     *
     * @throws \InvalidArgumentException if transition is not allowed
     */
    public function unpublish(Timetable $timetable, int $userId): Timetable {
        return DB::transaction(function () use ($timetable, $userId) {
            $timetable = Timetable::lockForUpdate()->findOrFail($timetable->id);

            if (!$this->isValidTransition($timetable->status, Timetable::STATUS_DRAFT)) {
                throw new \InvalidArgumentException(
                    "Cannot unpublish: timetable is currently '{$timetable->status}'."
                );
            }

            $timetable->update([
                'status' => Timetable::STATUS_DRAFT,
                'published_at' => null,
                'published_by' => null,
            ]);

            TimetableAuditLog::log($timetable, 'unpublished', 'Timetable reverted to draft');

            return $timetable->refresh();
        });
    }

    /**
     * Rollback to a previous version: snapshot current state as safety net,
     * delete all current slots, restore from version snapshot, set to draft.
     *
     * @throws \InvalidArgumentException if version does not belong to timetable
     */
    public function rollback(Timetable $timetable, int $versionId, int $userId): Timetable {
        return DB::transaction(function () use ($timetable, $versionId, $userId) {
            $timetable = Timetable::lockForUpdate()->findOrFail($timetable->id);
            $version = TimetableVersion::where('timetable_id', $timetable->id)
                ->findOrFail($versionId);

            // Guard against empty snapshots — rolling back to an empty version would wipe the timetable
            if (empty($version->snapshot_data)) {
                throw new \InvalidArgumentException('Cannot rollback: version snapshot is empty.');
            }

            // Snapshot current state as safety net before rollback
            $this->createVersionSnapshot($timetable, $userId, 'Auto-snapshot before rollback');

            // Delete all current slots
            TimetableSlot::where('timetable_id', $timetable->id)->delete();

            // Re-create slots from snapshot
            foreach ($version->snapshot_data as $slotData) {
                // Remove any 'id' key that might exist in snapshot
                unset($slotData['id']);
                TimetableSlot::create(array_merge($slotData, [
                    'timetable_id' => $timetable->id,
                ]));
            }

            app(TimetableIntegrityService::class)->forgetCachedAnalysis($timetable->id);

            // Set timetable back to draft after rollback
            $timetable->update([
                'status' => Timetable::STATUS_DRAFT,
                'published_at' => null,
                'published_by' => null,
            ]);

            TimetableAuditLog::log(
                $timetable,
                'rolled_back',
                "Rolled back to version {$version->version_number}",
                null,
                ['restored_version' => $version->version_number, 'slot_count' => $version->slot_count]
            );

            return $timetable->refresh();
        });
    }

    /**
     * Create a snapshot of all current slots as a new version record.
     */
    private function createVersionSnapshot(Timetable $timetable, int $userId, ?string $notes = null): TimetableVersion {
        $slots = TimetableSlot::where('timetable_id', $timetable->id)
            ->get([
                'klass_subject_id', 'optional_subject_id', 'teacher_id',
                'venue_id', 'assistant_teacher_id',
                'day_of_cycle', 'period_number', 'duration',
                'is_locked', 'block_id', 'coupling_group_key',
            ])
            ->toArray();

        $lastVersion = TimetableVersion::where('timetable_id', $timetable->id)
            ->max('version_number') ?? 0;

        return TimetableVersion::create([
            'timetable_id' => $timetable->id,
            'version_number' => $lastVersion + 1,
            'snapshot_data' => $slots,
            'slot_count' => count($slots),
            'notes' => $notes,
            'published_at' => now(),
            'published_by' => $userId,
        ]);
    }

    /**
     * Archive the currently published timetable for a given term.
     * Called inside publish() to enforce one-published-per-term constraint.
     */
    private function archiveCurrentPublished(int $termId, int $excludeId, int $userId): void {
        $current = Timetable::lockForUpdate()
            ->published()
            ->forTerm($termId)
            ->where('id', '!=', $excludeId)
            ->first();

        if (!$current) {
            return;
        }

        $current->update(['status' => Timetable::STATUS_ARCHIVED]);

        TimetableAuditLog::log(
            $current,
            'archived',
            'Archived: replaced by newly published timetable'
        );
    }

    /**
     * Check if a status transition is valid.
     */
    public function isValidTransition(string $from, string $to): bool {
        if (!isset(self::TRANSITIONS[$from])) {
            return false;
        }

        return in_array($to, self::TRANSITIONS[$from]);
    }

    /**
     * Get all allowed transitions from the current status.
     */
    public function getAllowedTransitions(string $currentStatus): array {
        return self::TRANSITIONS[$currentStatus] ?? [];
    }
}
