<?php

namespace App\Services\Schemes;

use App\Models\Grade;
use App\Models\GradeSubject;
use App\Models\KlassSubject;
use App\Models\OptionalSubject;
use App\Models\Schemes\SchemeOfWork;
use App\Models\Schemes\StandardScheme;
use App\Models\Schemes\StandardSchemeEntry;
use App\Models\Schemes\StandardSchemeWorkflowAudit;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class StandardSchemeService {
    public function __construct(
        protected StandardSchemeTeacherNotificationService $teacherNotificationService,
    ) {
    }

    /**
     * Create a new StandardScheme with auto-generated weekly entry rows.
     * Auto-adds creator as 'lead' contributor and subject teachers as 'viewer'.
     */
    public function createWithEntries(array $data, int $creatorId): StandardScheme {
        return DB::transaction(function () use ($data, $creatorId): StandardScheme {
            $totalWeeks = $data['total_weeks'] ?? 10;
            $lockedGradeSubject = $this->lockGradeSubjectContext(
                (int) $data['subject_id'],
                (int) $data['grade_id'],
                (int) $data['term_id']
            );
            $this->assertNoExistingStandardScheme(
                (int) $data['subject_id'],
                (int) $lockedGradeSubject->grade_id,
                (int) $data['term_id']
            );

            $scheme = StandardScheme::create([
                'subject_id'    => $data['subject_id'],
                'grade_id'      => $lockedGradeSubject->grade_id,
                'term_id'       => $data['term_id'],
                'department_id' => $lockedGradeSubject->department_id,
                'created_by'    => $creatorId,
                'panel_lead_id' => $data['panel_lead_id'] ?? $creatorId,
                'status'        => 'draft',
                'total_weeks'   => $totalWeeks,
            ]);

            // Bulk insert weekly entries
            $now = now();
            $entries = [];
            for ($week = 1; $week <= $totalWeeks; $week++) {
                $entries[] = [
                    'standard_scheme_id' => $scheme->id,
                    'week_number'        => $week,
                    'status'             => 'planned',
                    'created_at'         => $now,
                    'updated_at'         => $now,
                ];
            }
            StandardSchemeEntry::insert($entries);

            $this->seedDefaultContributors($scheme, $creatorId);

            return $scheme->load('entries');
        });
    }

    /**
     * Clone an existing standard scheme into a new term.
     *
     * Resolves the target grade by matching the source grade name inside the
     * selected term, then re-resolves the department from that term's
     * subject allocation before copying the weekly content.
     */
    public function cloneScheme(StandardScheme $source, int $creatorId, int $newTermId): StandardScheme {
        return DB::transaction(function () use ($source, $creatorId, $newTermId): StandardScheme {
            $fresh = StandardScheme::query()
                ->with([
                    'grade:id,name',
                    'entries.objectives:id',
                ])
                ->lockForUpdate()
                ->findOrFail($source->id);

            if ((int) $fresh->term_id === $newTermId) {
                throw new InvalidArgumentException('Please select a different term.');
            }

            $gradeName = trim((string) $fresh->grade?->name);
            if ($gradeName === '') {
                throw new InvalidArgumentException('The source scheme is missing a valid grade.');
            }

            $targetGrade = Grade::query()
                ->where('term_id', $newTermId)
                ->get(['id', 'name', 'term_id'])
                ->first(fn (Grade $grade) => strcasecmp(trim((string) $grade->name), $gradeName) === 0);

            if (!$targetGrade) {
                throw new InvalidArgumentException(
                    "Could not find grade '{$gradeName}' in the selected term."
                );
            }

            $targetGradeSubject = $this->lockGradeSubjectContext(
                (int) $fresh->subject_id,
                (int) $targetGrade->id,
                (int) $newTermId
            );
            $this->assertNoExistingStandardScheme(
                (int) $fresh->subject_id,
                (int) $targetGrade->id,
                (int) $newTermId
            );

            $clone = StandardScheme::create([
                'subject_id' => $fresh->subject_id,
                'grade_id' => $targetGrade->id,
                'term_id' => $newTermId,
                'department_id' => $targetGradeSubject->department_id,
                'created_by' => $creatorId,
                'panel_lead_id' => $creatorId,
                'status' => 'draft',
                'total_weeks' => $fresh->total_weeks,
            ]);

            foreach ($fresh->entries as $sourceEntry) {
                $newEntry = $clone->entries()->create([
                    'week_number' => $sourceEntry->week_number,
                    'syllabus_topic_id' => $sourceEntry->syllabus_topic_id,
                    'topic' => $sourceEntry->topic,
                    'sub_topic' => $sourceEntry->sub_topic,
                    'learning_objectives' => $sourceEntry->learning_objectives,
                    'status' => 'planned',
                ]);

                $objectiveIds = $sourceEntry->objectives->pluck('id')->all();
                if (!empty($objectiveIds)) {
                    $newEntry->objectives()->sync($objectiveIds);
                }
            }

            $this->seedDefaultContributors($clone, $creatorId);

            return $clone->load('entries.objectives');
        });
    }

    /**
     * Submit a standard scheme for review.
     */
    public function submitScheme(StandardScheme $scheme, User $actor, ?string $comments = null): void {
        DB::transaction(function () use ($scheme, $actor, $comments): void {
            $fresh = StandardScheme::lockForUpdate()->findOrFail($scheme->id);

            $allowed = ['draft', 'revision_required'];
            if (!in_array($fresh->status, $allowed, true)) {
                throw new InvalidArgumentException(
                    "Cannot submit standard scheme with status '{$fresh->status}'."
                );
            }

            $fromStatus = $fresh->status;

            $updates = ['status' => 'submitted'];
            if ($fromStatus === 'revision_required') {
                $updates['review_comments'] = null;
            }

            $fresh->update($updates);

            StandardSchemeWorkflowAudit::log(
                $fresh, $actor,
                StandardSchemeWorkflowAudit::ACTION_SUBMITTED,
                $fromStatus, 'submitted', $comments
            );
        });
    }

    /**
     * Place a submitted standard scheme under review.
     */
    public function placeUnderReview(StandardScheme $scheme, User $actor, ?string $comments = null): void {
        DB::transaction(function () use ($scheme, $actor, $comments): void {
            $fresh = StandardScheme::lockForUpdate()->findOrFail($scheme->id);

            if ($fresh->status !== 'submitted') {
                throw new InvalidArgumentException(
                    "Cannot place standard scheme under review with status '{$fresh->status}'."
                );
            }

            $fromStatus = $fresh->status;
            $fresh->update(['status' => 'under_review']);

            StandardSchemeWorkflowAudit::log(
                $fresh, $actor,
                StandardSchemeWorkflowAudit::ACTION_PLACED_UNDER_REVIEW,
                $fromStatus, 'under_review', $comments
            );
        });
    }

    /**
     * Approve a standard scheme that is under review.
     */
    public function approveScheme(StandardScheme $scheme, User $actor, ?string $comments = null): void {
        DB::transaction(function () use ($scheme, $actor, $comments): void {
            $fresh = StandardScheme::lockForUpdate()->findOrFail($scheme->id);

            if ($fresh->status !== 'under_review') {
                throw new InvalidArgumentException(
                    "Cannot approve standard scheme with status '{$fresh->status}'."
                );
            }

            $fromStatus = $fresh->status;
            $fresh->update([
                'status'      => 'approved',
                'reviewed_by' => $actor->id,
                'reviewed_at' => now(),
            ]);

            StandardSchemeWorkflowAudit::log(
                $fresh, $actor,
                StandardSchemeWorkflowAudit::ACTION_APPROVED,
                $fromStatus, 'approved', $comments
            );
        });
    }

    /**
     * Return a standard scheme for revision with mandatory comments.
     */
    public function returnForRevision(StandardScheme $scheme, User $actor, string $comments): void {
        DB::transaction(function () use ($scheme, $actor, $comments): void {
            $fresh = StandardScheme::lockForUpdate()->findOrFail($scheme->id);

            if ($fresh->status !== 'under_review') {
                throw new InvalidArgumentException(
                    "Cannot return standard scheme for revision with status '{$fresh->status}'."
                );
            }

            $fromStatus = $fresh->status;
            $fresh->update([
                'status'          => 'revision_required',
                'review_comments' => $comments,
                'reviewed_by'     => $actor->id,
                'reviewed_at'     => now(),
            ]);

            StandardSchemeWorkflowAudit::log(
                $fresh, $actor,
                StandardSchemeWorkflowAudit::ACTION_REVISION_REQUIRED,
                $fromStatus, 'revision_required', $comments
            );
        });
    }

    /**
     * Publish a standard scheme and automatically distribute to all teachers.
     * Sets published_at and published_by. If not yet approved, also sets status to 'approved'.
     * Returns the number of teacher schemes created by distribution.
     */
    public function publishScheme(StandardScheme $scheme, User $actor): int {
        // First publish
        DB::transaction(function () use ($scheme, $actor): void {
            $fresh = StandardScheme::lockForUpdate()->findOrFail($scheme->id);

            $fromStatus = $fresh->status;
            $updates = [
                'published_at' => now(),
                'published_by' => $actor->id,
            ];

            if ($fresh->status !== 'approved') {
                $updates['status'] = 'approved';
                $updates['reviewed_by'] = $actor->id;
                $updates['reviewed_at'] = now();
            }

            $fresh->update($updates);

            StandardSchemeWorkflowAudit::log(
                $fresh, $actor,
                StandardSchemeWorkflowAudit::ACTION_PUBLISHED,
                $fromStatus, $fresh->status
            );
        });

        // Then distribute (needs a fresh instance with status = approved)
        $scheme->refresh();
        return $this->distributeToTeachers($scheme, $actor);
    }

    /**
     * Unpublish a published standard scheme.
     * Clears published_at and published_by.
     */
    public function unpublishScheme(StandardScheme $scheme, User $actor): void {
        DB::transaction(function () use ($scheme, $actor): void {
            $fresh = StandardScheme::lockForUpdate()->findOrFail($scheme->id);

            if (!$fresh->isPublished()) {
                return;
            }

            $fresh->update([
                'published_at' => null,
                'published_by' => null,
            ]);

            StandardSchemeWorkflowAudit::log(
                $fresh, $actor,
                StandardSchemeWorkflowAudit::ACTION_UNPUBLISHED,
                $fresh->status, $fresh->status
            );
        });
    }

    /**
     * Distribute an approved standard scheme to all teachers of the subject+grade+term.
     *
     * Creates individual SchemeOfWork records linked back to the standard scheme,
     * with entries copied and objective pivots replicated.
     *
     * Idempotent: skips teacher assignments that already have a linked scheme.
     *
     * @return int Number of teacher schemes created
     */
    public function distributeToTeachers(StandardScheme $scheme, User $actor): int {
        return DB::transaction(function () use ($scheme, $actor): int {
            $fresh = StandardScheme::lockForUpdate()->findOrFail($scheme->id);

            if ($fresh->status !== 'approved') {
                throw new InvalidArgumentException(
                    "Cannot distribute standard scheme with status '{$fresh->status}'."
                );
            }

            $fresh->load('entries.objectives');

            // Find all GradeSubject rows matching subject + grade + term
            $gradeSubjectIds = GradeSubject::query()
                ->where('subject_id', $fresh->subject_id)
                ->where('term_id', $fresh->term_id)
                ->whereHas('grade', function ($q) use ($fresh): void {
                    $q->where('id', $fresh->grade_id);
                })
                ->pluck('id');

            // Collect all teacher assignments (KlassSubject + OptionalSubject)
            $assignments = collect();

            $klassSubjects = KlassSubject::query()
                ->with('klass:id,name')
                ->whereIn('grade_subject_id', $gradeSubjectIds)
                ->where('term_id', $fresh->term_id)
                ->where('active', true)
                ->get();

            foreach ($klassSubjects as $ks) {
                $assignments->push([
                    'teacher_id'         => $ks->user_id,
                    'klass_subject_id'   => $ks->id,
                    'optional_subject_id' => null,
                    'term_id'            => $fresh->term_id,
                    'label'              => $this->formatAssignmentLabel($ks, null),
                ]);
            }

            $optionalSubjects = OptionalSubject::query()
                ->whereIn('grade_subject_id', $gradeSubjectIds)
                ->where('term_id', $fresh->term_id)
                ->get();

            foreach ($optionalSubjects as $os) {
                $assignments->push([
                    'teacher_id'         => $os->user_id,
                    'klass_subject_id'   => null,
                    'optional_subject_id' => $os->id,
                    'term_id'            => $fresh->term_id,
                    'label'              => $this->formatAssignmentLabel(null, $os),
                ]);
            }

            $createdCount = 0;
            $notificationManifest = [];

            foreach ($assignments as $assignment) {
                // Skip assignments without a teacher
                if (empty($assignment['teacher_id'])) {
                    continue;
                }

                $this->lockAssignmentRow(
                    $assignment['klass_subject_id'],
                    $assignment['optional_subject_id']
                );

                // Respect the one-active-scheme-per-assignment-per-term rule for all schemes,
                // not only schemes already linked to this standard scheme.
                if ($this->assignmentAlreadyHasScheme(
                    $assignment['klass_subject_id'],
                    $assignment['optional_subject_id'],
                    (int) $assignment['term_id']
                )) {
                    continue;
                }

                $teacherScheme = SchemeOfWork::create([
                    'klass_subject_id'    => $assignment['klass_subject_id'],
                    'optional_subject_id' => $assignment['optional_subject_id'],
                    'term_id'             => $assignment['term_id'],
                    'teacher_id'          => $assignment['teacher_id'],
                    'status'              => 'approved',
                    'total_weeks'         => $fresh->total_weeks,
                    'standard_scheme_id'  => $fresh->id,
                ]);

                // Copy entries with standard_scheme_entry_id link
                foreach ($fresh->entries as $standardEntry) {
                    $newEntry = $teacherScheme->entries()->create([
                        'week_number'                => $standardEntry->week_number,
                        'syllabus_topic_id'          => $standardEntry->syllabus_topic_id,
                        'standard_scheme_entry_id'   => $standardEntry->id,
                        'topic'                      => $standardEntry->topic,
                        'sub_topic'                  => $standardEntry->sub_topic,
                        'learning_objectives'        => $standardEntry->learning_objectives,
                        'status'                     => 'planned',
                    ]);

                    // Copy objective pivots
                    $objectiveIds = $standardEntry->objectives->pluck('id')->toArray();
                    if (!empty($objectiveIds)) {
                        $newEntry->objectives()->sync($objectiveIds);
                    }
                }

                $this->appendNotificationManifestEntry(
                    $notificationManifest,
                    (int) $assignment['teacher_id'],
                    $teacherScheme->id,
                    (string) ($assignment['label'] ?? 'Teaching assignment')
                );

                $createdCount++;
            }

            // Log the distribution
            StandardSchemeWorkflowAudit::log(
                $fresh,
                $actor,
                StandardSchemeWorkflowAudit::ACTION_DISTRIBUTED,
                $fresh->status,
                $fresh->status,
                "Distributed to {$createdCount} teacher(s)"
            );

            if ($createdCount > 0 && !empty($notificationManifest)) {
                $schemeId = (int) $fresh->id;
                $actorId = (int) $actor->id;

                DB::afterCommit(function () use ($schemeId, $actorId, $notificationManifest): void {
                    $standardScheme = StandardScheme::query()
                        ->with([
                            'subject:id,name',
                            'grade:id,name',
                            'term:id,term,year',
                        ])
                        ->find($schemeId);

                    $publisher = User::query()->find($actorId);

                    if (!$standardScheme || !$publisher) {
                        Log::warning('Skipping standard scheme distribution notifications because source models are missing.', [
                            'standard_scheme_id' => $schemeId,
                            'actor_id' => $actorId,
                        ]);

                        return;
                    }

                    $this->teacherNotificationService->notifyTeachers(
                        $standardScheme,
                        $publisher,
                        $notificationManifest
                    );
                });
            }

            return $createdCount;
        });
    }

    private function seedDefaultContributors(StandardScheme $scheme, int $creatorId): void
    {
        $contributors = [
            $creatorId => ['role' => 'lead'],
        ];

        foreach ($scheme->getTeachersForSubject() as $teacher) {
            if ((int) $teacher->id === $creatorId) {
                continue;
            }

            $contributors[$teacher->id] = ['role' => 'viewer'];
        }

        $scheme->contributors()->syncWithoutDetaching($contributors);
    }

    private function lockGradeSubjectContext(int $subjectId, int $gradeId, int $termId): GradeSubject
    {
        $gradeSubject = GradeSubject::query()
            ->where('subject_id', $subjectId)
            ->where('grade_id', $gradeId)
            ->where('term_id', $termId)
            ->lockForUpdate()
            ->first();

        if (!$gradeSubject || is_null($gradeSubject->department_id)) {
            throw new InvalidArgumentException(
                'Could not resolve a department for this subject and grade combination in the selected term.'
            );
        }

        return $gradeSubject;
    }

    private function assertNoExistingStandardScheme(int $subjectId, int $gradeId, int $termId): void
    {
        $existingScheme = StandardScheme::withTrashed()
            ->where('subject_id', $subjectId)
            ->where('grade_id', $gradeId)
            ->where('term_id', $termId)
            ->first();

        if ($existingScheme) {
            if (method_exists($existingScheme, 'trashed') && $existingScheme->trashed()) {
                throw new InvalidArgumentException(
                    'A deleted standard scheme already exists for this subject, grade, and selected term. Restore or permanently remove it before creating another.'
                );
            }

            throw new InvalidArgumentException(
                'A standard scheme already exists for this subject, grade, and selected term.'
            );
        }
    }

    private function lockAssignmentRow(?int $klassSubjectId, ?int $optionalSubjectId): void
    {
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

    private function assignmentAlreadyHasScheme(?int $klassSubjectId, ?int $optionalSubjectId, int $termId): bool
    {
        $query = SchemeOfWork::query()->where('term_id', $termId);

        if ($klassSubjectId) {
            $query->where('klass_subject_id', $klassSubjectId);
        } elseif ($optionalSubjectId) {
            $query->where('optional_subject_id', $optionalSubjectId);
        } else {
            throw new InvalidArgumentException('The target assignment is missing.');
        }

        return $query->exists();
    }

    private function appendNotificationManifestEntry(array &$manifest, int $teacherId, int $schemeId, string $label): void
    {
        if (!isset($manifest[$teacherId])) {
            $manifest[$teacherId] = [
                'teacher_id' => $teacherId,
                'scheme_ids' => [],
                'items' => [],
            ];
        }

        $manifest[$teacherId]['scheme_ids'][] = $schemeId;
        $manifest[$teacherId]['items'][] = [
            'scheme_id' => $schemeId,
            'label' => $label,
        ];
    }

    private function formatAssignmentLabel(?KlassSubject $klassSubject, ?OptionalSubject $optionalSubject): string
    {
        if ($klassSubject) {
            $klassName = trim((string) optional($klassSubject->klass)->name);

            return $klassName !== '' ? "Class {$klassName}" : 'Class Assignment';
        }

        $optionalName = trim((string) optional($optionalSubject)->name);

        return $optionalName !== '' ? "Optional Group {$optionalName}" : 'Optional Subject Assignment';
    }
}
