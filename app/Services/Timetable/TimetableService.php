<?php

namespace App\Services\Timetable;

use App\Models\Timetable\Timetable;
use App\Models\Timetable\TimetableAuditLog;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TimetableService {
    /**
     * List timetables, optionally filtered by term.
     */
    public function list(?int $termId = null): Collection {
        $query = Timetable::with('creator');

        if ($termId) {
            $query->where('term_id', $termId);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Find a timetable by ID with all relationships loaded.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function find(int $id): Timetable {
        return Timetable::with([
            'slots',
            'constraints',
            'conflicts',
            'creator',
            'term',
        ])->findOrFail($id);
    }

    /**
     * Create a new draft timetable.
     */
    public function create(array $data, int $userId): Timetable {
        return DB::transaction(function () use ($data, $userId) {
            $timetable = Timetable::create([
                'name' => $data['name'],
                'term_id' => $data['term_id'],
                'status' => Timetable::STATUS_DRAFT,
                'created_by' => $userId,
            ]);

            TimetableAuditLog::log($timetable, 'created', 'Timetable created');

            return $timetable->fresh(['creator', 'term']);
        });
    }

    /**
     * Update an existing timetable.
     */
    public function update(Timetable $timetable, array $data): Timetable {
        return DB::transaction(function () use ($timetable, $data) {
            $oldValues = [
                'name' => $timetable->name,
                'status' => $timetable->status,
            ];

            $timetable->update($data);

            $newValues = [
                'name' => $timetable->name,
                'status' => $timetable->status,
            ];

            TimetableAuditLog::log(
                $timetable,
                'updated',
                'Timetable updated',
                $oldValues,
                $newValues
            );

            return $timetable->refresh();
        });
    }

    /**
     * Soft-delete a timetable.
     */
    public function delete(Timetable $timetable): bool {
        return DB::transaction(function () use ($timetable) {
            TimetableAuditLog::log($timetable, 'deleted', 'Timetable soft-deleted');

            $timetable->delete();

            return true;
        });
    }
}
