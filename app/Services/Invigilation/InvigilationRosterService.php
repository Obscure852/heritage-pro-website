<?php

namespace App\Services\Invigilation;

use App\Models\Grade;
use App\Models\GradeSubject;
use App\Models\KlassSubject;
use App\Models\OptionalSubject;
use App\Models\User;
use App\Models\Venue;
use App\Models\Invigilation\InvigilationAssignment;
use App\Models\Invigilation\InvigilationSeries;
use App\Models\Invigilation\InvigilationSession;
use App\Models\Invigilation\InvigilationSessionRoom;
use App\Models\Timetable\Timetable;
use App\Models\Timetable\TimetableSlot;
use App\Services\SchoolModeResolver;
use App\Services\Timetable\PeriodSettingsService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InvigilationRosterService
{
    public function __construct(
        protected SchoolModeResolver $schoolModeResolver,
        protected PeriodSettingsService $periodSettingsService
    ) {
    }

    public function supportedLevels(): array
    {
        return $this->schoolModeResolver->supportedLevels();
    }

    public function visibleGrades(int $termId): Collection
    {
        return Grade::query()
            ->where('term_id', $termId)
            ->where('active', true)
            ->whereIn('level', $this->supportedLevels())
            ->orderBy('sequence')
            ->get();
    }

    public function gradeSubjectOptions(int $termId): Collection
    {
        return GradeSubject::query()
            ->with(['grade:id,name,sequence,level', 'subject:id,name'])
            ->where('term_id', $termId)
            ->where('active', true)
            ->whereHas('grade', function (Builder $query) use ($termId): void {
                $query->where('term_id', $termId)
                    ->where('active', true)
                    ->whereIn('level', $this->supportedLevels());
            })
            ->orderBy('grade_id')
            ->orderBy('sequence')
            ->get();
    }

    public function teacherOptions(): Collection
    {
        return User::teachingAndCurrent()
            ->orderBy('firstname')
            ->orderBy('lastname')
            ->get();
    }

    public function venueOptions(): Collection
    {
        return Venue::query()->orderBy('name')->get();
    }

    public function klassSubjectOptions(InvigilationSession $session): Collection
    {
        return $this->klassSubjectOptionsForGradeSubject($session->series->term_id, $session->grade_subject_id);
    }

    public function optionalSubjectOptions(InvigilationSession $session): Collection
    {
        return $this->optionalSubjectOptionsForGradeSubject($session->series->term_id, $session->grade_subject_id);
    }

    public function klassSubjectOptionsForGradeSubject(int $termId, int $gradeSubjectId): Collection
    {
        return KlassSubject::query()
            ->with(['klass:id,name', 'teacher:id,firstname,lastname'])
            ->where('term_id', $termId)
            ->where('active', true)
            ->where('grade_subject_id', $gradeSubjectId)
            ->orderBy('klass_id')
            ->get();
    }

    public function optionalSubjectOptionsForGradeSubject(int $termId, int $gradeSubjectId): Collection
    {
        return OptionalSubject::query()
            ->with(['teacher:id,firstname,lastname'])
            ->where('term_id', $termId)
            ->where('active', true)
            ->where('grade_subject_id', $gradeSubjectId)
            ->orderBy('name')
            ->get();
    }

    public function loadSeriesDetail(InvigilationSeries $series): InvigilationSeries
    {
        $series->load([
            'term',
            'creator:id,firstname,lastname',
            'publisher:id,firstname,lastname',
            'sessions.gradeSubject.subject',
            'sessions.gradeSubject.grade',
            'sessions.rooms.venue',
            'sessions.rooms.klassSubject.klass.students',
            'sessions.rooms.klassSubject.teacher:id,firstname,lastname',
            'sessions.rooms.optionalSubject.students',
            'sessions.rooms.optionalSubject.teacher:id,firstname,lastname',
            'sessions.rooms.assignments.user:id,firstname,lastname',
        ]);

        return $series;
    }

    public function detailMetrics(InvigilationSeries $series): array
    {
        $series = $this->loadSeriesDetail($series);

        $sessionCount = $series->sessions->count();
        $roomCount = $series->sessions->sum(fn (InvigilationSession $session) => $session->rooms->count());
        $requiredSlots = $series->sessions->flatMap->rooms->sum('required_invigilators');
        $assignedSlots = $series->sessions->flatMap->rooms->sum(fn (InvigilationSessionRoom $room) => $room->assignments->count());
        $coverage = $requiredSlots > 0 ? round(($assignedSlots / $requiredSlots) * 100, 1) : 0;

        return [
            'sessions' => $sessionCount,
            'rooms' => $roomCount,
            'required_slots' => $requiredSlots,
            'assigned_slots' => $assignedSlots,
            'coverage' => $coverage,
        ];
    }

    public function issueSummary(InvigilationSeries $series): array
    {
        $issues = $this->buildIssues($series);

        return [
            'shortages' => count($issues['shortages']),
            'teacher_conflicts' => count($issues['teacher_conflicts']),
            'room_conflicts' => count($issues['room_conflicts']),
            'eligibility_conflicts' => count($issues['eligibility_conflicts']),
            'timetable_conflicts' => count($issues['timetable_conflicts']),
            'blocking_conflicts' => count($this->blockingIssues($issues)),
        ];
    }

    public function validateRoomPayload(InvigilationSession $session, array $payload, ?InvigilationSessionRoom $room = null): array
    {
        $series = $session->series;
        $sourceType = $payload['source_type'];
        $validated = [
            'venue_id' => (int) $payload['venue_id'],
            'source_type' => $sourceType,
            'klass_subject_id' => null,
            'optional_subject_id' => null,
            'group_label' => trim((string) ($payload['group_label'] ?? '')),
            'candidate_count' => isset($payload['candidate_count']) && $payload['candidate_count'] !== ''
                ? (int) $payload['candidate_count']
                : null,
            'required_invigilators' => (int) ($payload['required_invigilators'] ?? $series->default_required_invigilators),
        ];

        if ($validated['required_invigilators'] < 1) {
            $this->fail(['required_invigilators' => 'Each room must require at least one invigilator.']);
        }

        if ($sourceType === InvigilationSessionRoom::SOURCE_KLASS_SUBJECT) {
            $seriesYear = (int) ($series->term?->year ?? $series->term()->value('year'));
            $klassSubject = KlassSubject::query()
                ->with('klass')
                ->find($payload['klass_subject_id'] ?? null);

            if (!$klassSubject) {
                $this->fail([
                    'klass_subject_id' => 'Select the specific class allocation for this room, such as F1A Science. If the room is mixed, change Source Type to Manual / Mixed Room.',
                ]);
            }

            if ((int) $klassSubject->grade_subject_id !== (int) $session->grade_subject_id || (int) $klassSubject->term_id !== (int) $series->term_id) {
                $this->fail(['klass_subject_id' => 'The selected class subject does not match this exam session subject or term.']);
            }

            $validated['klass_subject_id'] = (int) $klassSubject->id;
            $validated['group_label'] = $validated['group_label'] !== '' ? $validated['group_label'] : ($klassSubject->klass?->name ?? 'Class Group');
            $validated['candidate_count'] ??= $klassSubject->klass
                ? $klassSubject->klass->currentStudents($series->term_id, $seriesYear)->count()
                : 0;
        } elseif ($sourceType === InvigilationSessionRoom::SOURCE_OPTIONAL_SUBJECT) {
            $optionalSubject = OptionalSubject::query()->find($payload['optional_subject_id'] ?? null);

            if (!$optionalSubject) {
                $this->fail(['optional_subject_id' => 'Select a valid optional subject group.']);
            }

            if ((int) $optionalSubject->grade_subject_id !== (int) $session->grade_subject_id || (int) $optionalSubject->term_id !== (int) $series->term_id) {
                $this->fail(['optional_subject_id' => 'The selected optional subject does not match this exam session subject or term.']);
            }

            $validated['optional_subject_id'] = (int) $optionalSubject->id;
            $validated['group_label'] = $validated['group_label'] !== '' ? $validated['group_label'] : ($optionalSubject->name ?? 'Optional Group');
            $validated['candidate_count'] ??= $optionalSubject->students()->wherePivot('term_id', $series->term_id)->count();
        } else {
            $validated['group_label'] = $validated['group_label'] !== '' ? $validated['group_label'] : 'Manual Group';
            $validated['candidate_count'] ??= 0;
        }

        if ($validated['candidate_count'] < 0) {
            $this->fail(['candidate_count' => 'Candidate count cannot be negative.']);
        }

        if ($room && $room->assignments()->count() > $validated['required_invigilators']) {
            $this->fail(['required_invigilators' => 'Reduce assignments first before lowering the room staffing requirement.']);
        }

        $this->assertRoomVenueDoesNotOverlap($session, $validated['venue_id'], $room);

        return $validated;
    }

    public function generateAssignments(InvigilationSeries $series, ?int $actorId = null): array
    {
        $series = $this->loadSeriesDetail($series);
        $publishedTimetable = $this->publishedTimetableForSeries($series);

        if ($series->timetable_conflict_policy === InvigilationSeries::TIMETABLE_CHECK && !$publishedTimetable) {
            $this->fail([
                'timetable' => 'A published teaching timetable is required before generating invigilation assignments with timetable checks enabled.',
            ]);
        }

        return DB::transaction(function () use ($series, $publishedTimetable) {
            $roomIds = $series->sessions->flatMap->rooms->pluck('id')->all();

            InvigilationAssignment::query()
                ->whereIn('session_room_id', $roomIds)
                ->where('assignment_source', InvigilationAssignment::SOURCE_AUTO)
                ->where('locked', false)
                ->delete();

            $series = $this->loadSeriesDetail($series->fresh());
            $teachers = $this->teacherOptions()->keyBy('id');
            $teacherSchedules = $this->buildTeacherSchedules($series);
            $seriesLoad = $this->buildSeriesLoad($series);
            $dailyLoad = $this->buildDailyLoad($series);
            $created = 0;
            $shortages = [];

            foreach ($series->sessions as $session) {
                foreach ($session->rooms as $room) {
                    $existingAssignments = $room->assignments;
                    $needed = max($room->required_invigilators - $existingAssignments->count(), 0);

                    if ($needed === 0) {
                        continue;
                    }

                    $availableOrders = $this->nextAssignmentOrders($room, $needed);

                    foreach ($availableOrders as $order) {
                        $candidate = $this->pickBestCandidate(
                            $series,
                            $session,
                            $room,
                            $teachers,
                            $teacherSchedules,
                            $seriesLoad,
                            $dailyLoad,
                            $publishedTimetable
                        );

                        if (!$candidate) {
                            $shortages[] = [
                                'session' => $session->display_name,
                                'exam_date' => $session->exam_date?->format('Y-m-d'),
                                'start_time' => $session->start_time,
                                'venue' => $room->venue?->name,
                                'group' => $room->resolved_group_label,
                            ];
                            break;
                        }

                        $assignment = $room->assignments()->create([
                            'user_id' => $candidate->id,
                            'assignment_order' => $order,
                            'assignment_source' => InvigilationAssignment::SOURCE_AUTO,
                            'locked' => false,
                        ]);

                        $created++;

                        $dateKey = $session->exam_date?->format('Y-m-d') ?? '';
                        $teacherSchedules[$candidate->id][$dateKey][] = [
                            'start' => $session->start_time,
                            'end' => $session->end_time,
                            'room_id' => $room->id,
                            'assignment_id' => $assignment->id,
                        ];
                        $seriesLoad[$candidate->id] = ($seriesLoad[$candidate->id] ?? 0) + 1;
                        $dailyLoad[$candidate->id][$dateKey] = ($dailyLoad[$candidate->id][$dateKey] ?? 0) + 1;
                    }
                }
            }

            return [
                'created' => $created,
                'shortages' => $shortages,
            ];
        });
    }

    public function publish(InvigilationSeries $series, ?int $actorId = null): array
    {
        $series = $this->loadSeriesDetail($series);

        if (!$series->isDraft()) {
            $this->fail([
                'publish' => 'Only draft invigilation series can be published.',
            ]);
        }

        if ($series->sessions->isEmpty()) {
            $this->fail([
                'publish' => 'Add at least one exam session before publishing this invigilation series.',
            ]);
        }

        $issues = $this->buildIssues($series);
        $blockingIssues = $this->blockingIssues($issues);

        if (!empty($blockingIssues)) {
            $this->fail([
                'publish' => 'Resolve all shortages and conflicts before publishing this invigilation series.',
            ]);
        }

        $series->update([
            'status' => InvigilationSeries::STATUS_PUBLISHED,
            'published_at' => now(),
            'published_by' => $actorId,
        ]);

        return $this->detailMetrics($series->fresh());
    }

    public function unpublish(InvigilationSeries $series): array
    {
        $series = $this->loadSeriesDetail($series);

        if (!$series->isPublished()) {
            $this->fail([
                'unpublish' => 'Only published invigilation series can be unpublished.',
            ]);
        }

        $series->update([
            'status' => InvigilationSeries::STATUS_DRAFT,
            'published_at' => null,
            'published_by' => null,
        ]);

        return $this->detailMetrics($series->fresh());
    }

    public function buildDailyReport(InvigilationSeries $series): Collection
    {
        return $this->dailyReportRows($series)
            ->groupBy('date')
            ->map(fn (Collection $items): Collection => $items->values());
    }

    public function buildDailyTimetableMatrix(InvigilationSeries $series): array
    {
        $rows = $this->dailyReportRows($series);
        $dates = $rows->pluck('date')->filter()->unique()->values();
        $timeSlots = $rows
            ->map(fn (array $row): array => [
                'key' => $this->dailyTimeSlotKey($row['start_time'], $row['end_time']),
                'label' => $this->normalizeReportTime($row['start_time']) . ' - ' . $this->normalizeReportTime($row['end_time']),
                'start_time' => $row['start_time'],
                'end_time' => $row['end_time'],
            ])
            ->unique('key')
            ->sortBy(fn (array $slot): string => sprintf('%s %s', $slot['start_time'], $slot['end_time']))
            ->values();

        $cells = [];

        foreach ($timeSlots as $slot) {
            $cells[$slot['key']] = [];

            foreach ($dates as $date) {
                $cells[$slot['key']][$date] = $rows
                    ->filter(fn (array $row): bool => $row['date'] === $date
                        && $this->dailyTimeSlotKey($row['start_time'], $row['end_time']) === $slot['key'])
                    ->values();
            }
        }

        return [
            'dates' => $dates,
            'time_slots' => $timeSlots,
            'cells' => $cells,
        ];
    }

    public function buildTeacherReport(InvigilationSeries $series): Collection
    {
        return $this->teacherReportRows($series)
            ->groupBy('teacher')
            ->sortKeys();
    }

    public function buildTeacherTimetableMatrix(InvigilationSeries $series): array
    {
        $rows = $this->teacherReportRows($series);
        $dates = $rows->pluck('date')->filter()->unique()->values();
        $resourceRows = $rows
            ->pluck('teacher')
            ->filter()
            ->unique()
            ->sortBy(fn (string $teacher): string => strtolower($teacher))
            ->values()
            ->map(fn (string $teacher): array => [
                'key' => $teacher,
                'label' => $teacher,
                'count' => $rows->where('teacher', $teacher)->count(),
            ]);

        $cells = [];

        foreach ($resourceRows as $resourceRow) {
            $cells[$resourceRow['key']] = [];

            foreach ($dates as $date) {
                $cells[$resourceRow['key']][$date] = $rows
                    ->filter(fn (array $row): bool => $row['teacher'] === $resourceRow['key']
                        && $row['date'] === $date)
                    ->values();
            }
        }

        return [
            'dates' => $dates,
            'resource_rows' => $resourceRows,
            'cells' => $cells,
        ];
    }

    public function buildRoomTimetableMatrix(InvigilationSeries $series): array
    {
        $rows = $this->roomReportRows($series);
        $dates = $rows->pluck('date')->filter()->unique()->values();
        $resourceRows = $rows
            ->pluck('venue')
            ->filter()
            ->unique()
            ->sortBy(fn (string $venue): string => strtolower($venue))
            ->map(fn (string $venue): array => [
                'key' => $venue,
                'label' => $venue,
                'count' => $rows->where('venue', $venue)->count(),
            ])
            ->values();

        $cells = [];

        foreach ($resourceRows as $resourceRow) {
            $cells[$resourceRow['key']] = [];

            foreach ($dates as $date) {
                $cells[$resourceRow['key']][$date] = $rows
                    ->filter(fn (array $row): bool => $row['venue'] === $resourceRow['key']
                        && $row['date'] === $date)
                    ->values();
            }
        }

        return [
            'dates' => $dates,
            'resource_rows' => $resourceRows,
            'cells' => $cells,
        ];
    }

    public function buildRoomReport(InvigilationSeries $series): Collection
    {
        return $this->roomReportRows($series)
            ->groupBy('venue')
            ->map(fn (Collection $items): Collection => $items->values());
    }

    public function buildConflictReport(InvigilationSeries $series): Collection
    {
        return collect($this->flattenIssues($this->buildIssues($series)));
    }

    public function buildIssues(InvigilationSeries $series): array
    {
        $series = $this->loadSeriesDetail($series);
        $issues = [
            'shortages' => [],
            'teacher_conflicts' => [],
            'room_conflicts' => [],
            'eligibility_conflicts' => [],
            'timetable_conflicts' => [],
        ];

        $publishedTimetable = $this->publishedTimetableForSeries($series);
        $rooms = $series->sessions->flatMap->rooms->values();

        foreach ($rooms as $room) {
            $session = $room->session;

            if ($series->timetable_conflict_policy === InvigilationSeries::TIMETABLE_CHECK && !$session->day_of_cycle) {
                $issues['timetable_conflicts'][] = [
                    'title' => 'Missing day of cycle',
                    'detail' => sprintf(
                        '%s on %s is missing the timetable day of cycle required for timetable clash checks.',
                        $session->display_name,
                        $session->exam_date?->format('Y-m-d')
                    ),
                ];
            }

            if ($room->assignments->count() < $room->required_invigilators) {
                $issues['shortages'][] = [
                    'title' => 'Room understaffed',
                    'detail' => sprintf(
                        '%s in %s has %d of %d invigilators assigned.',
                        $session->display_name,
                        $room->venue?->name ?? 'Unknown Venue',
                        $room->assignments->count(),
                        $room->required_invigilators
                    ),
                ];
            }

            foreach ($room->assignments as $assignment) {
                if (!$this->teacherMatchesPolicy($assignment->user_id, $room, $series)) {
                    $issues['eligibility_conflicts'][] = [
                        'title' => 'Eligibility policy mismatch',
                        'detail' => sprintf(
                            '%s is not valid for %s in %s under the current eligibility policy.',
                            $assignment->user?->full_name ?? 'Unknown Teacher',
                            $session->display_name,
                            $room->venue?->name ?? 'Unknown Venue'
                        ),
                    ];
                }

                if ($series->timetable_conflict_policy === InvigilationSeries::TIMETABLE_CHECK) {
                    if (!$publishedTimetable) {
                        $issues['timetable_conflicts'][] = [
                            'title' => 'Published timetable missing',
                            'detail' => 'Timetable conflict checking is enabled, but no published timetable exists for this term.',
                        ];
                    } elseif ($this->teacherHasTimetableConflict($assignment->user_id, $session, $series, $publishedTimetable)) {
                        $issues['timetable_conflicts'][] = [
                            'title' => 'Teaching timetable clash',
                            'detail' => sprintf(
                                '%s has a teaching timetable clash with %s on %s.',
                                $assignment->user?->full_name ?? 'Unknown Teacher',
                                $session->display_name,
                                $session->exam_date?->format('Y-m-d')
                            ),
                        ];
                    }
                }
            }
        }

        $roomRows = $rooms->map(function (InvigilationSessionRoom $room): array {
            $session = $room->session;

            return [
                'room_id' => $room->id,
                'venue_id' => $room->venue_id,
                'venue' => $room->venue?->name ?? 'Unknown Venue',
                'date' => $session->exam_date?->format('Y-m-d') ?? '',
                'start' => $session->start_time,
                'end' => $session->end_time,
                'subject' => $session->display_name,
            ];
        })->values();

        for ($i = 0; $i < $roomRows->count(); $i++) {
            for ($j = $i + 1; $j < $roomRows->count(); $j++) {
                $left = $roomRows[$i];
                $right = $roomRows[$j];

                if ($left['venue_id'] !== $right['venue_id'] || $left['date'] !== $right['date']) {
                    continue;
                }

                if ($this->intervalsOverlap($left['start'], $left['end'], $right['start'], $right['end'])) {
                    $issues['room_conflicts'][] = [
                        'title' => 'Room overlap',
                        'detail' => sprintf(
                            '%s is double-booked for %s and %s on %s.',
                            $left['venue'],
                            $left['subject'],
                            $right['subject'],
                            $left['date']
                        ),
                    ];
                }
            }
        }

        $teacherRows = $series->sessions
            ->flatMap(function (InvigilationSession $session): Collection {
                return $session->rooms->flatMap(function (InvigilationSessionRoom $room) use ($session): Collection {
                    return $room->assignments->map(function (InvigilationAssignment $assignment) use ($session, $room): array {
                        return [
                            'teacher_id' => $assignment->user_id,
                            'teacher' => $assignment->user?->full_name ?? 'Unknown Teacher',
                            'date' => $session->exam_date?->format('Y-m-d') ?? '',
                            'start' => $session->start_time,
                            'end' => $session->end_time,
                            'venue' => $room->venue?->name ?? 'Unknown Venue',
                            'subject' => $session->display_name,
                        ];
                    });
                });
            })
            ->values();

        for ($i = 0; $i < $teacherRows->count(); $i++) {
            for ($j = $i + 1; $j < $teacherRows->count(); $j++) {
                $left = $teacherRows[$i];
                $right = $teacherRows[$j];

                if ($left['teacher_id'] !== $right['teacher_id'] || $left['date'] !== $right['date']) {
                    continue;
                }

                if ($this->intervalsOverlap($left['start'], $left['end'], $right['start'], $right['end'])) {
                    $issues['teacher_conflicts'][] = [
                        'title' => 'Teacher overlap',
                        'detail' => sprintf(
                            '%s is assigned to overlapping duties for %s and %s on %s.',
                            $left['teacher'],
                            $left['subject'],
                            $right['subject'],
                            $left['date']
                        ),
                    ];
                }
            }
        }

        foreach ($issues as $key => $values) {
            $issues[$key] = collect($values)->unique('detail')->values()->all();
        }

        return $issues;
    }

    public function flattenIssues(array $issues): array
    {
        $flattened = [];

        foreach ($issues as $category => $entries) {
            foreach ($entries as $entry) {
                $flattened[] = [
                    'category' => str_replace('_', ' ', $category),
                    'title' => $entry['title'],
                    'detail' => $entry['detail'],
                ];
            }
        }

        return $flattened;
    }

    public function nextAssignmentOrders(InvigilationSessionRoom $room, int $count): array
    {
        $existingOrders = $room->assignments()->pluck('assignment_order')->map(fn ($value) => (int) $value)->all();
        $orders = [];
        $next = 1;

        while (count($orders) < $count) {
            if (!in_array($next, $existingOrders, true)) {
                $orders[] = $next;
            }
            $next++;
        }

        return $orders;
    }

    public function roomHasCapacityForAssignment(InvigilationSessionRoom $room): bool
    {
        return $room->assignments()->count() < $room->required_invigilators;
    }

    public function teacherOverlapsAnotherDuty(int $teacherId, InvigilationSessionRoom $room, ?int $ignoreAssignmentId = null): bool
    {
        $session = $room->session()->firstOrFail();

        return InvigilationAssignment::query()
            ->where('user_id', $teacherId)
            ->when($ignoreAssignmentId, fn (Builder $query) => $query->where('id', '!=', $ignoreAssignmentId))
            ->whereHas('sessionRoom.session', function (Builder $query) use ($session): void {
                $query->whereDate('exam_date', $session->exam_date)
                    ->where(function (Builder $overlapQuery) use ($session): void {
                        $overlapQuery
                            ->where('start_time', '<', $session->end_time)
                            ->where('end_time', '>', $session->start_time);
                    });
            })
            ->exists();
    }

    public function teacherMatchesPolicy(int $teacherId, InvigilationSessionRoom $room, InvigilationSeries $series): bool
    {
        if ($series->eligibility_policy === InvigilationSeries::POLICY_ANY_TEACHER) {
            return true;
        }

        $subjectTeacherIds = $this->subjectTeacherIdsForRoom($room, $series);
        $isSubjectTeacher = in_array($teacherId, $subjectTeacherIds, true);

        return match ($series->eligibility_policy) {
            InvigilationSeries::POLICY_SUBJECT_ONLY => $isSubjectTeacher,
            InvigilationSeries::POLICY_EXCLUDE_SUBJECT_TEACHERS => !$isSubjectTeacher,
            default => true,
        };
    }

    public function teacherHasTimetableConflict(
        int $teacherId,
        InvigilationSession $session,
        InvigilationSeries $series,
        ?Timetable $publishedTimetable = null
    ): bool {
        $publishedTimetable ??= $this->publishedTimetableForSeries($series);

        if (!$publishedTimetable || !$session->day_of_cycle) {
            return false;
        }

        $periodNumbers = $this->overlappingPeriodNumbers($session->start_time, $session->end_time);
        if (empty($periodNumbers)) {
            return false;
        }

        return TimetableSlot::query()
            ->where('timetable_id', $publishedTimetable->id)
            ->where('day_of_cycle', $session->day_of_cycle)
            ->whereIn('period_number', $periodNumbers)
            ->where(function (Builder $query) use ($teacherId): void {
                $query->where('teacher_id', $teacherId)
                    ->orWhere('assistant_teacher_id', $teacherId);
            })
            ->exists();
    }

    protected function pickBestCandidate(
        InvigilationSeries $series,
        InvigilationSession $session,
        InvigilationSessionRoom $room,
        Collection $teachers,
        array $teacherSchedules,
        array $seriesLoad,
        array $dailyLoad,
        ?Timetable $publishedTimetable
    ): ?User {
        $dateKey = $session->exam_date?->format('Y-m-d') ?? '';

        $eligible = $teachers->filter(function (User $teacher) use (
            $series,
            $session,
            $room,
            $teacherSchedules,
            $publishedTimetable,
            $dateKey
        ): bool {
            if (!$this->teacherMatchesPolicy($teacher->id, $room, $series)) {
                return false;
            }

            if ($series->timetable_conflict_policy === InvigilationSeries::TIMETABLE_CHECK
                && $this->teacherHasTimetableConflict($teacher->id, $session, $series, $publishedTimetable)) {
                return false;
            }

            foreach ($teacherSchedules[$teacher->id][$dateKey] ?? [] as $slot) {
                if ($this->intervalsOverlap($slot['start'], $slot['end'], $session->start_time, $session->end_time)) {
                    return false;
                }
            }

            return true;
        })->values();

        if ($eligible->isEmpty()) {
            return null;
        }

        $sorted = $eligible->sortBy(function (User $teacher) use ($seriesLoad, $dailyLoad, $teacherSchedules, $dateKey, $session): array {
            return [
                (int) ($seriesLoad[$teacher->id] ?? 0),
                (int) ($dailyLoad[$teacher->id][$dateKey] ?? 0),
                $this->backToBackCount($teacher->id, $dateKey, $session->start_time, $session->end_time, $teacherSchedules),
                strtolower($teacher->full_name),
                (int) $teacher->id,
            ];
        });

        return $sorted->first();
    }

    protected function buildTeacherSchedules(InvigilationSeries $series): array
    {
        $schedules = [];

        foreach ($series->sessions as $session) {
            $dateKey = $session->exam_date?->format('Y-m-d') ?? '';

            foreach ($session->rooms as $room) {
                foreach ($room->assignments as $assignment) {
                    $schedules[$assignment->user_id][$dateKey][] = [
                        'start' => $session->start_time,
                        'end' => $session->end_time,
                        'room_id' => $room->id,
                        'assignment_id' => $assignment->id,
                    ];
                }
            }
        }

        return $schedules;
    }

    protected function buildSeriesLoad(InvigilationSeries $series): array
    {
        $load = [];

        foreach ($series->sessions as $session) {
            foreach ($session->rooms as $room) {
                foreach ($room->assignments as $assignment) {
                    $load[$assignment->user_id] = ($load[$assignment->user_id] ?? 0) + 1;
                }
            }
        }

        return $load;
    }

    protected function dailyReportRows(InvigilationSeries $series): Collection
    {
        $series = $this->loadSeriesDetail($series);

        return $series->sessions
            ->flatMap(function (InvigilationSession $session): Collection {
                return $session->rooms->map(function (InvigilationSessionRoom $room) use ($session): array {
                    return [
                        'date' => $session->exam_date?->format('Y-m-d'),
                        'start_time' => $session->start_time,
                        'end_time' => $session->end_time,
                        'subject' => $session->display_name,
                        'grade' => $session->gradeSubject?->grade?->name,
                        'venue' => $room->venue?->name,
                        'group' => $room->resolved_group_label,
                        'required' => $room->required_invigilators,
                        'assigned' => $room->assignments->count(),
                        'invigilators' => $room->assignments->map(fn (InvigilationAssignment $assignment) => $assignment->user?->full_name)->filter()->values()->all(),
                    ];
                });
            })
            ->sortBy(fn (array $row): string => sprintf(
                '%s %s %s %s',
                $row['date'] ?? '9999-12-31',
                $row['start_time'] ?? '23:59:59',
                $row['end_time'] ?? '23:59:59',
                strtolower((string) ($row['venue'] ?? ''))
            ))
            ->values();
    }

    protected function teacherReportRows(InvigilationSeries $series): Collection
    {
        $series = $this->loadSeriesDetail($series);

        return $series->sessions
            ->flatMap(function (InvigilationSession $session): Collection {
                return $session->rooms->flatMap(function (InvigilationSessionRoom $room) use ($session): Collection {
                    return $room->assignments->map(function (InvigilationAssignment $assignment) use ($session, $room): array {
                        return [
                            'teacher_id' => $assignment->user_id,
                            'teacher' => $assignment->user?->full_name ?? 'Unknown Teacher',
                            'date' => $session->exam_date?->format('Y-m-d'),
                            'start_time' => $session->start_time,
                            'end_time' => $session->end_time,
                            'subject' => $session->display_name,
                            'grade' => $session->gradeSubject?->grade?->name,
                            'venue' => $room->venue?->name,
                            'group' => $room->resolved_group_label,
                            'locked' => $assignment->locked,
                            'source' => $assignment->assignment_source,
                        ];
                    });
                });
            })
            ->sortBy(fn (array $row): string => sprintf(
                '%s %s %s %s %s',
                $row['date'] ?? '9999-12-31',
                $row['start_time'] ?? '23:59:59',
                $row['end_time'] ?? '23:59:59',
                strtolower((string) ($row['teacher'] ?? '')),
                strtolower((string) ($row['venue'] ?? ''))
            ))
            ->values();
    }

    protected function roomReportRows(InvigilationSeries $series): Collection
    {
        $series = $this->loadSeriesDetail($series);

        return $series->sessions
            ->flatMap(function (InvigilationSession $session): Collection {
                return $session->rooms->map(function (InvigilationSessionRoom $room) use ($session): array {
                    return [
                        'venue' => $room->venue?->name ?? 'Unknown Venue',
                        'date' => $session->exam_date?->format('Y-m-d'),
                        'start_time' => $session->start_time,
                        'end_time' => $session->end_time,
                        'subject' => $session->display_name,
                        'grade' => $session->gradeSubject?->grade?->name,
                        'group' => $room->resolved_group_label,
                        'candidate_count' => $room->candidate_count,
                        'required' => $room->required_invigilators,
                        'assigned' => $room->assignments->count(),
                        'invigilators' => $room->assignments->map(fn (InvigilationAssignment $assignment) => $assignment->user?->full_name)->filter()->values()->all(),
                    ];
                });
            })
            ->sortBy(fn (array $row): string => sprintf(
                '%s %s %s %s',
                strtolower((string) ($row['venue'] ?? '')),
                $row['date'] ?? '9999-12-31',
                $row['start_time'] ?? '23:59:59',
                $row['end_time'] ?? '23:59:59'
            ))
            ->values();
    }

    protected function buildDailyLoad(InvigilationSeries $series): array
    {
        $load = [];

        foreach ($series->sessions as $session) {
            $dateKey = $session->exam_date?->format('Y-m-d') ?? '';

            foreach ($session->rooms as $room) {
                foreach ($room->assignments as $assignment) {
                    $load[$assignment->user_id][$dateKey] = ($load[$assignment->user_id][$dateKey] ?? 0) + 1;
                }
            }
        }

        return $load;
    }

    protected function subjectTeacherIdsForRoom(InvigilationSessionRoom $room, InvigilationSeries $series): array
    {
        if ($room->source_type === InvigilationSessionRoom::SOURCE_KLASS_SUBJECT && $room->klassSubject) {
            return collect([
                $room->klassSubject->user_id,
                $room->klassSubject->assistant_user_id,
            ])->filter()->map(fn ($value) => (int) $value)->unique()->values()->all();
        }

        if ($room->source_type === InvigilationSessionRoom::SOURCE_OPTIONAL_SUBJECT && $room->optionalSubject) {
            return collect([
                $room->optionalSubject->user_id,
                $room->optionalSubject->assistant_user_id,
            ])->filter()->map(fn ($value) => (int) $value)->unique()->values()->all();
        }

        return collect()
            ->merge(
                KlassSubject::query()
                    ->where('term_id', $series->term_id)
                    ->where('active', true)
                    ->where('grade_subject_id', $room->session?->grade_subject_id)
                    ->pluck('user_id')
            )
            ->merge(
                KlassSubject::query()
                    ->where('term_id', $series->term_id)
                    ->where('active', true)
                    ->where('grade_subject_id', $room->session?->grade_subject_id)
                    ->pluck('assistant_user_id')
            )
            ->merge(
                OptionalSubject::query()
                    ->where('term_id', $series->term_id)
                    ->where('active', true)
                    ->where('grade_subject_id', $room->session?->grade_subject_id)
                    ->pluck('user_id')
            )
            ->merge(
                OptionalSubject::query()
                    ->where('term_id', $series->term_id)
                    ->where('active', true)
                    ->where('grade_subject_id', $room->session?->grade_subject_id)
                    ->pluck('assistant_user_id')
            )
            ->filter()
            ->map(fn ($value) => (int) $value)
            ->unique()
            ->values()
            ->all();
    }

    protected function publishedTimetableForSeries(InvigilationSeries $series): ?Timetable
    {
        return Timetable::query()
            ->published()
            ->forTerm($series->term_id)
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->first();
    }

    protected function overlappingPeriodNumbers(string $startTime, string $endTime): array
    {
        return collect($this->periodSettingsService->getDaySchedule())
            ->filter(fn (array $item) => ($item['type'] ?? null) === 'period')
            ->filter(function (array $period) use ($startTime, $endTime): bool {
                return $this->intervalsOverlap($startTime, $endTime, $period['start_time'], $period['end_time']);
            })
            ->pluck('period')
            ->map(fn ($period) => (int) $period)
            ->values()
            ->all();
    }

    protected function backToBackCount(int $teacherId, string $dateKey, string $startTime, string $endTime, array $teacherSchedules): int
    {
        $count = 0;

        foreach ($teacherSchedules[$teacherId][$dateKey] ?? [] as $slot) {
            if ($slot['end'] === $startTime || $slot['start'] === $endTime) {
                $count++;
            }
        }

        return $count;
    }

    protected function intervalsOverlap(string $startA, string $endA, string $startB, string $endB): bool
    {
        return $this->timeToMinutes($startA) < $this->timeToMinutes($endB)
            && $this->timeToMinutes($endA) > $this->timeToMinutes($startB);
    }

    protected function timeToMinutes(string $time): int
    {
        $parts = explode(':', substr($time, 0, 5));
        $hours = (int) ($parts[0] ?? 0);
        $minutes = (int) ($parts[1] ?? 0);

        return ($hours * 60) + $minutes;
    }

    protected function dailyTimeSlotKey(?string $startTime, ?string $endTime): string
    {
        return $this->normalizeReportTime($startTime) . '-' . $this->normalizeReportTime($endTime);
    }

    protected function normalizeReportTime(?string $time): string
    {
        return substr((string) $time, 0, 5);
    }

    protected function assertRoomVenueDoesNotOverlap(
        InvigilationSession $session,
        int $venueId,
        ?InvigilationSessionRoom $ignoreRoom = null
    ): void {
        $conflictExists = InvigilationSessionRoom::query()
            ->where('venue_id', $venueId)
            ->when($ignoreRoom, fn (Builder $query) => $query->where('id', '!=', $ignoreRoom->id))
            ->whereHas('session', function (Builder $query) use ($session): void {
                $query->whereDate('exam_date', $session->exam_date)
                    ->where('start_time', '<', $session->end_time)
                    ->where('end_time', '>', $session->start_time);
            })
            ->exists();

        if ($conflictExists) {
            $this->fail([
                'venue_id' => 'The selected venue is already assigned to another overlapping exam room.',
            ]);
        }
    }

    protected function blockingIssues(array $issues): array
    {
        return array_merge(
            $issues['shortages'],
            $issues['teacher_conflicts'],
            $issues['room_conflicts'],
            $issues['eligibility_conflicts'],
            $issues['timetable_conflicts'],
        );
    }

    protected function fail(array $messages): never
    {
        throw ValidationException::withMessages($messages);
    }
}
