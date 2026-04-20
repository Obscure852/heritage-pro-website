<?php

namespace App\Services\Schemes;

use App\Models\KlassSubject;
use App\Models\OptionalSubject;
use App\Models\Schemes\SchemeOfWork;
use App\Models\Schemes\SchemeOfWorkEntry;
use App\Models\Schemes\SchemeWorkflowAudit;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SchemeService {
    /**
     * Create a new SchemeOfWork with auto-generated weekly entry rows.
     *
     * Uses bulk insert (not create() in a loop) for performance.
     */
    public function createWithEntries(array $data, int $teacherId): SchemeOfWork {
        return DB::transaction(function () use ($data, $teacherId): SchemeOfWork {
            $this->lockAssignmentRow($data['klass_subject_id'] ?? null, $data['optional_subject_id'] ?? null);
            $this->assertNoExistingScheme(
                $data['klass_subject_id'] ?? null,
                $data['optional_subject_id'] ?? null,
                (int) $data['term_id']
            );

            $totalWeeks = $data['total_weeks'] ?? 10;

            $scheme = SchemeOfWork::create([
                'klass_subject_id'    => $data['klass_subject_id'] ?? null,
                'optional_subject_id' => $data['optional_subject_id'] ?? null,
                'term_id'             => $data['term_id'],
                'teacher_id'          => $teacherId,
                'status'              => 'draft',
                'total_weeks'         => $totalWeeks,
            ]);

            $now = now();
            $entries = [];
            for ($week = 1; $week <= $totalWeeks; $week++) {
                $entries[] = [
                    'scheme_of_work_id' => $scheme->id,
                    'week_number'       => $week,
                    'status'            => 'planned',
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ];
            }

            SchemeOfWorkEntry::insert($entries);

            return $scheme;
        });
    }

    /**
     * Clone an existing SchemeOfWork into a new term.
     *
     * Copies all entry content and objective pivots. Each entry is created
     * individually (not bulk insert) so we have IDs for pivot sync.
     *
     * @throws InvalidArgumentException if a scheme already exists for the assignment in the target term.
     */
    public function cloneScheme(SchemeOfWork $source, int $teacherId, int $newTermId): SchemeOfWork {
        return DB::transaction(function () use ($source, $teacherId, $newTermId): SchemeOfWork {
            $this->lockAssignmentRow($source->klass_subject_id, $source->optional_subject_id);
            $this->assertNoExistingScheme($source->klass_subject_id, $source->optional_subject_id, $newTermId);

            $clone = SchemeOfWork::create([
                'klass_subject_id'    => $source->klass_subject_id,
                'optional_subject_id' => $source->optional_subject_id,
                'term_id'             => $newTermId,
                'teacher_id'          => $teacherId,
                'status'              => 'draft',
                'total_weeks'         => $source->total_weeks,
                'cloned_from_id'      => $source->id,
            ]);

            // Load source entries with their objectives for copying
            $source->load('entries.objectives');

            foreach ($source->entries as $sourceEntry) {
                $newEntry = $clone->entries()->create([
                    'week_number'          => $sourceEntry->week_number,
                    'syllabus_topic_id'    => $sourceEntry->syllabus_topic_id,
                    'topic'                => $sourceEntry->topic,
                    'sub_topic'            => $sourceEntry->sub_topic,
                    'learning_objectives'  => $sourceEntry->learning_objectives,
                    'status'               => 'planned',
                ]);

                $objectiveIds = $sourceEntry->objectives->pluck('id')->toArray();
                if (!empty($objectiveIds)) {
                    $newEntry->objectives()->sync($objectiveIds);
                }
            }

            return $clone->load('entries.objectives');
        });
    }

    /**
     * Transition a scheme from draft or revision_required to submitted.
     * Clears review_comments when resubmitting after revision.
     *
     * @throws InvalidArgumentException if scheme is not in an allowed source status.
     */
    public function submitScheme(SchemeOfWork $scheme, User $actor, ?string $comments = null): void {
        DB::transaction(function () use ($scheme, $actor, $comments): void {
            $fresh = SchemeOfWork::lockForUpdate()->find($scheme->id);

            $allowed = ['draft', 'revision_required'];
            if (!in_array($fresh->status, $allowed, true)) {
                throw new InvalidArgumentException(
                    "Cannot submit scheme with status '{$fresh->status}'."
                );
            }

            $fromStatus = $fresh->status;

            $updates = ['status' => 'submitted'];
            if ($fromStatus === 'revision_required') {
                $updates['review_comments'] = null;
                $updates['supervisor_comments'] = null;
            }

            $fresh->update($updates);

            SchemeWorkflowAudit::log($fresh, $actor, SchemeWorkflowAudit::ACTION_SUBMITTED, $fromStatus, 'submitted', $comments);
        });
    }

    /**
     * Transition a scheme from submitted to under_review.
     *
     * @throws InvalidArgumentException if scheme is not in submitted status.
     */
    public function placeUnderReview(SchemeOfWork $scheme, User $actor, ?string $comments = null): void {
        DB::transaction(function () use ($scheme, $actor, $comments): void {
            $fresh = SchemeOfWork::lockForUpdate()->find($scheme->id);

            $allowed = ['submitted', 'supervisor_reviewed'];
            if (!in_array($fresh->status, $allowed, true)) {
                throw new InvalidArgumentException(
                    "Cannot place scheme under review with status '{$fresh->status}'."
                );
            }

            $fromStatus = $fresh->status;
            $fresh->update(['status' => 'under_review']);

            SchemeWorkflowAudit::log($fresh, $actor, SchemeWorkflowAudit::ACTION_PLACED_UNDER_REVIEW, $fromStatus, 'under_review', $comments);
        });
    }

    /**
     * Approve a scheme that is currently under_review.
     * Records reviewer and timestamp.
     *
     * @throws InvalidArgumentException if scheme is not in under_review status.
     */
    public function approveScheme(SchemeOfWork $scheme, User $actor, ?string $comments = null): void {
        DB::transaction(function () use ($scheme, $actor, $comments): void {
            $fresh = SchemeOfWork::lockForUpdate()->find($scheme->id);

            if ($fresh->status !== 'under_review') {
                throw new InvalidArgumentException(
                    "Cannot approve scheme with status '{$fresh->status}'."
                );
            }

            $fromStatus = $fresh->status;
            $fresh->update([
                'status'      => 'approved',
                'reviewed_by' => $actor->id,
                'reviewed_at' => now(),
            ]);

            SchemeWorkflowAudit::log($fresh, $actor, SchemeWorkflowAudit::ACTION_APPROVED, $fromStatus, 'approved', $comments);
        });
    }

    /**
     * Publish an approved scheme as the reference scheme for its subject/grade/term.
     * Only one scheme can be published per grade_subject + term context.
     */
    public function publishReference(SchemeOfWork $scheme, User $actor): void
    {
        DB::transaction(function () use ($scheme, $actor): void {
            $fresh = SchemeOfWork::query()->lockForUpdate()->findOrFail($scheme->id);

            if ($fresh->status !== 'approved') {
                throw new InvalidArgumentException(
                    "Cannot publish scheme with status '{$fresh->status}'."
                );
            }

            $gradeSubjectId = $fresh->gradeSubject?->id;
            if (!$gradeSubjectId) {
                throw new InvalidArgumentException('The scheme is missing a valid subject context.');
            }

            $matchingIds = SchemeOfWork::query()
                ->where('term_id', $fresh->term_id)
                ->forGradeSubject($gradeSubjectId)
                ->lockForUpdate()
                ->pluck('schemes_of_work.id')
                ->all();

            if (!empty($matchingIds)) {
                SchemeOfWork::query()
                    ->whereIn('id', $matchingIds)
                    ->update([
                        'is_published' => false,
                        'published_at' => null,
                        'published_by' => null,
                        'updated_at' => now(),
                    ]);
            }

            $fresh->update([
                'is_published' => true,
                'published_at' => now(),
                'published_by' => $actor->id,
            ]);

            SchemeWorkflowAudit::log(
                $fresh,
                $actor,
                SchemeWorkflowAudit::ACTION_REFERENCE_PUBLISHED,
                $fresh->status,
                $fresh->status
            );
        });
    }

    /**
     * Remove a scheme from the published reference slot.
     */
    public function unpublishReference(SchemeOfWork $scheme, User $actor): void
    {
        DB::transaction(function () use ($scheme, $actor): void {
            $fresh = SchemeOfWork::query()->lockForUpdate()->findOrFail($scheme->id);

            if ($fresh->status !== 'approved') {
                throw new InvalidArgumentException(
                    "Cannot unpublish scheme with status '{$fresh->status}'."
                );
            }

            if (!$fresh->is_published) {
                return;
            }

            $fresh->update([
                'is_published' => false,
                'published_at' => null,
                'published_by' => null,
            ]);

            SchemeWorkflowAudit::log(
                $fresh,
                $actor,
                SchemeWorkflowAudit::ACTION_REFERENCE_UNPUBLISHED,
                $fresh->status,
                $fresh->status
            );
        });
    }

    /**
     * Return a scheme for revision from under_review status.
     * Stores HOD comments and records reviewer and timestamp.
     *
     * @throws InvalidArgumentException if scheme is not in under_review status.
     */
    public function returnForRevision(SchemeOfWork $scheme, User $actor, string $comments): void {
        DB::transaction(function () use ($scheme, $actor, $comments): void {
            $fresh = SchemeOfWork::lockForUpdate()->find($scheme->id);

            if ($fresh->status !== 'under_review') {
                throw new InvalidArgumentException(
                    "Cannot return scheme for revision with status '{$fresh->status}'."
                );
            }

            $fromStatus = $fresh->status;
            $fresh->update([
                'status'          => 'revision_required',
                'review_comments' => $comments,
                'reviewed_by'     => $actor->id,
                'reviewed_at'     => now(),
            ]);

            SchemeWorkflowAudit::log($fresh, $actor, SchemeWorkflowAudit::ACTION_REVISION_REQUIRED, $fromStatus, 'revision_required', $comments);
        });
    }

    /**
     * Supervisor approves a submitted scheme, forwarding it to the HOD queue.
     */
    public function supervisorApprove(SchemeOfWork $scheme, User $actor, ?string $comments = null): void {
        DB::transaction(function () use ($scheme, $actor, $comments): void {
            $fresh = SchemeOfWork::lockForUpdate()->find($scheme->id);

            if ($fresh->status !== 'submitted') {
                throw new InvalidArgumentException(
                    "Cannot supervisor-approve scheme with status '{$fresh->status}'."
                );
            }

            $fromStatus = $fresh->status;
            $fresh->update([
                'status'                => 'supervisor_reviewed',
                'supervisor_reviewed_by' => $actor->id,
                'supervisor_reviewed_at' => now(),
                'supervisor_comments'    => $comments,
            ]);

            SchemeWorkflowAudit::log($fresh, $actor, SchemeWorkflowAudit::ACTION_SUPERVISOR_APPROVED, $fromStatus, 'supervisor_reviewed', $comments);
        });

        // Bust HOD dashboard cache so scheme appears immediately
        $scheme->refresh();
        $scheme->load('teacher');
        $this->bustHodCacheForScheme($scheme);
    }

    /**
     * Supervisor returns a submitted scheme for revision.
     */
    public function supervisorReturnForRevision(SchemeOfWork $scheme, User $actor, ?string $comments = null): void {
        DB::transaction(function () use ($scheme, $actor, $comments): void {
            $fresh = SchemeOfWork::lockForUpdate()->find($scheme->id);

            if ($fresh->status !== 'submitted') {
                throw new InvalidArgumentException(
                    "Cannot return scheme for revision with status '{$fresh->status}'."
                );
            }

            $fromStatus = $fresh->status;
            $fresh->update([
                'status'                 => 'revision_required',
                'supervisor_comments'    => $comments,
                'supervisor_reviewed_by' => $actor->id,
                'supervisor_reviewed_at' => now(),
            ]);

            SchemeWorkflowAudit::log($fresh, $actor, SchemeWorkflowAudit::ACTION_SUPERVISOR_RETURNED, $fromStatus, 'revision_required', $comments);
        });

        // Bust HOD dashboard cache so stale entries are cleared
        $scheme->refresh();
        $scheme->load('teacher');
        $this->bustHodCacheForScheme($scheme);
    }

    /**
     * Bust HOD dashboard cache for the department(s) related to a scheme.
     */
    private function bustHodCacheForScheme(SchemeOfWork $scheme): void {
        $gradeSubject = $scheme->gradeSubject;
        if (!$gradeSubject || is_null($gradeSubject->department_id)) {
            return;
        }

        $department = \App\Models\Department::find($gradeSubject->department_id);
        if (!$department) {
            return;
        }

        $hodIds = array_filter([(int) $department->department_head, (int) $department->assistant]);
        foreach ($hodIds as $hodId) {
            if ($hodId > 0) {
                \Illuminate\Support\Facades\Cache::forget("hod_schemes_{$hodId}_{$scheme->term_id}");
            }
        }
    }

    private function lockAssignmentRow(?int $klassSubjectId, ?int $optionalSubjectId): void {
        if ($klassSubjectId) {
            KlassSubject::query()->lockForUpdate()->findOrFail($klassSubjectId);
            return;
        }

        if ($optionalSubjectId) {
            OptionalSubject::query()->lockForUpdate()->findOrFail($optionalSubjectId);
            return;
        }

        throw new InvalidArgumentException('The target assignment is missing.');
    }

    private function assertNoExistingScheme(?int $klassSubjectId, ?int $optionalSubjectId, int $termId): void {
        $query = SchemeOfWork::query()->where('term_id', $termId);

        if ($klassSubjectId) {
            $query->where('klass_subject_id', $klassSubjectId);
        } elseif ($optionalSubjectId) {
            $query->where('optional_subject_id', $optionalSubjectId);
        } else {
            throw new InvalidArgumentException('The target assignment is missing.');
        }

        if ($query->exists()) {
            throw new InvalidArgumentException(
                'A scheme already exists for this assignment in the selected term.'
            );
        }
    }
}
