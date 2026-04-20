<?php

namespace Tests\Feature\Invigilation;

use App\Models\Grade;
use App\Models\GradeSubject;
use App\Models\OptionalSubject;
use App\Models\SchoolSetup;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use App\Models\Venue;
use App\Models\Invigilation\InvigilationSeries;
use App\Models\Invigilation\InvigilationSessionRoom;
use App\Models\Timetable\Timetable;
use App\Models\Timetable\TimetableSetting;
use App\Models\Timetable\TimetableSlot;
use App\Services\Invigilation\InvigilationRosterService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\Concerns\EnsuresInvigilationSchema;
use Tests\TestCase;

class InvigilationRosterServiceTest extends TestCase
{
    use DatabaseTransactions;
    use EnsuresInvigilationSchema;

    private InvigilationRosterService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureInvigilationSchema();
        $this->service = app(InvigilationRosterService::class);

        SchoolSetup::query()->updateOrCreate(
            ['id' => 1],
            [
                'school_name' => 'Merementsi Junior Secondary School',
                'type' => SchoolSetup::TYPE_JUNIOR,
            ]
        );
    }

    public function test_generate_assignments_balances_workload_across_available_teachers(): void
    {
        [$term, $gradeSubject] = $this->createAcademicContext();
        $alpha = $this->createTeacher('Alpha', 'Teacher');
        $bravo = $this->createTeacher('Bravo', 'Teacher');
        $this->limitCandidatePoolTo([$alpha->id, $bravo->id]);
        $hallA = $this->createVenue('Hall A');
        $hallB = $this->createVenue('Hall B');

        $series = $this->createSeries($term, InvigilationSeries::POLICY_ANY_TEACHER);
        $firstRoom = $this->createManualRoom($series, $gradeSubject, $hallA, '2026-05-10', '08:00', '09:00', 'Group A');
        $secondRoom = $this->createManualRoom($series, $gradeSubject, $hallB, '2026-05-10', '09:10', '10:10', 'Group B');

        $result = $this->service->generateAssignments($series);

        $this->assertSame(2, $result['created']);
        $this->assertSame([], $result['shortages']);

        $this->assertSame(1, $firstRoom->fresh()->assignments()->count());
        $this->assertSame(1, $secondRoom->fresh()->assignments()->count());

        $assignmentUserIds = collect([
            $firstRoom->fresh()->assignments()->value('user_id'),
            $secondRoom->fresh()->assignments()->value('user_id'),
        ]);

        $this->assertCount(2, $assignmentUserIds->unique());
        $this->assertEqualsCanonicalizing([$alpha->id, $bravo->id], $assignmentUserIds->all());
    }

    public function test_generate_assignments_can_limit_to_subject_teachers_only(): void
    {
        [$term, $gradeSubject, $grade] = $this->createAcademicContext(includeGrade: true);
        $subjectTeacher = $this->createTeacher('Subject', 'Teacher');
        $otherTeacher = $this->createTeacher('Other', 'Teacher');
        $this->limitCandidatePoolTo([$subjectTeacher->id, $otherTeacher->id]);
        $venue = $this->createVenue('Hall A');

        OptionalSubject::query()->create([
            'name' => 'Mathematics Option',
            'grade_subject_id' => $gradeSubject->id,
            'user_id' => $subjectTeacher->id,
            'term_id' => $term->id,
            'grade_id' => $grade->id,
            'venue_id' => $venue->id,
            'active' => true,
        ]);

        $series = $this->createSeries($term, InvigilationSeries::POLICY_SUBJECT_ONLY);
        $room = $this->createManualRoom($series, $gradeSubject, $venue, '2026-05-11', '08:00', '09:00', 'Manual Group');

        $this->service->generateAssignments($series);

        $this->assertSame($subjectTeacher->id, $room->fresh()->assignments()->value('user_id'));
        $this->assertNotSame($otherTeacher->id, $room->fresh()->assignments()->value('user_id'));
    }

    public function test_generate_assignments_can_exclude_subject_teachers(): void
    {
        [$term, $gradeSubject, $grade] = $this->createAcademicContext(includeGrade: true);
        $subjectTeacher = $this->createTeacher('Subject', 'Teacher');
        $freeTeacher = $this->createTeacher('Free', 'Teacher');
        $this->limitCandidatePoolTo([$subjectTeacher->id, $freeTeacher->id]);
        $venue = $this->createVenue('Hall A');

        OptionalSubject::query()->create([
            'name' => 'Mathematics Option',
            'grade_subject_id' => $gradeSubject->id,
            'user_id' => $subjectTeacher->id,
            'term_id' => $term->id,
            'grade_id' => $grade->id,
            'venue_id' => $venue->id,
            'active' => true,
        ]);

        $series = $this->createSeries($term, InvigilationSeries::POLICY_EXCLUDE_SUBJECT_TEACHERS);
        $room = $this->createManualRoom($series, $gradeSubject, $venue, '2026-05-12', '08:00', '09:00', 'Manual Group');

        $this->service->generateAssignments($series);

        $this->assertSame($freeTeacher->id, $room->fresh()->assignments()->value('user_id'));
    }

    public function test_generate_assignments_skips_teachers_with_timetable_clashes_when_enabled(): void
    {
        [$term, $gradeSubject] = $this->createAcademicContext();
        $busyTeacher = $this->createTeacher('Busy', 'Teacher');
        $freeTeacher = $this->createTeacher('Free', 'Teacher');
        $this->limitCandidatePoolTo([$busyTeacher->id, $freeTeacher->id]);
        $venue = $this->createVenue('Hall A');

        TimetableSetting::set('period_definitions', [
            ['period' => 1, 'start_time' => '08:00', 'end_time' => '09:00', 'duration' => 60],
        ]);
        TimetableSetting::set('break_intervals', []);

        $timetable = Timetable::query()->create([
            'term_id' => $term->id,
            'name' => 'Published Timetable',
            'status' => Timetable::STATUS_PUBLISHED,
            'published_at' => now(),
            'published_by' => $busyTeacher->id,
            'created_by' => $busyTeacher->id,
        ]);

        TimetableSlot::query()->create([
            'timetable_id' => $timetable->id,
            'teacher_id' => $busyTeacher->id,
            'day_of_cycle' => 1,
            'period_number' => 1,
            'duration' => 1,
            'is_locked' => false,
        ]);

        $series = $this->createSeries(
            $term,
            InvigilationSeries::POLICY_ANY_TEACHER,
            InvigilationSeries::TIMETABLE_CHECK
        );
        $room = $this->createManualRoom($series, $gradeSubject, $venue, '2026-05-13', '08:15', '08:45', 'Manual Group', 1, 1);

        $result = $this->service->generateAssignments($series);

        $this->assertSame(1, $result['created']);
        $this->assertSame([], $result['shortages']);
        $this->assertSame($freeTeacher->id, $room->fresh()->assignments()->value('user_id'));
    }

    public function test_publish_throws_when_series_has_uncovered_rooms(): void
    {
        [$term, $gradeSubject] = $this->createAcademicContext();
        $series = $this->createSeries($term, InvigilationSeries::POLICY_ANY_TEACHER);

        $this->createManualRoom(
            $series,
            $gradeSubject,
            $this->createVenue('Hall A'),
            '2026-05-14',
            '08:00',
            '09:00',
            'Manual Group'
        );

        try {
            $this->service->publish($series);
            $this->fail('Expected publish validation failure.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('publish', $exception->errors());
        }
    }

    public function test_publish_throws_when_series_has_no_sessions(): void
    {
        [$term] = $this->createAcademicContext();
        $series = $this->createSeries($term, InvigilationSeries::POLICY_ANY_TEACHER);

        try {
            $this->service->publish($series);
            $this->fail('Expected publish validation failure.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('publish', $exception->errors());
        }
    }

    public function test_daily_timetable_matrix_groups_rooms_by_date_and_exact_time_slot(): void
    {
        [$term, $gradeSubject] = $this->createAcademicContext();
        $series = $this->createSeries($term, InvigilationSeries::POLICY_ANY_TEACHER);

        $this->createManualRoom($series, $gradeSubject, $this->createVenue('Hall A'), '2026-05-10', '08:00', '09:00', 'Group A');
        $this->createManualRoom($series, $gradeSubject, $this->createVenue('Hall B'), '2026-05-10', '08:00', '09:00', 'Group B');
        $this->createManualRoom($series, $gradeSubject, $this->createVenue('Hall C'), '2026-05-11', '10:00', '11:00', 'Group C');

        $matrix = $this->service->buildDailyTimetableMatrix($series);

        $this->assertSame(['2026-05-10', '2026-05-11'], $matrix['dates']->all());
        $this->assertSame(['08:00 - 09:00', '10:00 - 11:00'], $matrix['time_slots']->pluck('label')->all());
        $this->assertCount(2, $matrix['cells']['08:00-09:00']['2026-05-10']);
        $this->assertCount(0, $matrix['cells']['08:00-09:00']['2026-05-11']);
        $this->assertCount(1, $matrix['cells']['10:00-11:00']['2026-05-11']);
    }

    public function test_room_timetable_matrix_groups_sessions_by_room_then_date(): void
    {
        [$term, $gradeSubject] = $this->createAcademicContext();
        $series = $this->createSeries($term, InvigilationSeries::POLICY_ANY_TEACHER);

        $this->createManualRoom($series, $gradeSubject, $this->createVenue('Hall A'), '2026-05-10', '08:00', '09:00', 'Group A');
        $this->createManualRoom($series, $gradeSubject, $this->createVenue('Hall B'), '2026-05-10', '08:00', '09:00', 'Group B');
        $this->createManualRoom($series, $gradeSubject, $this->createVenue('Hall C'), '2026-05-11', '10:00', '11:00', 'Group C');

        $matrix = $this->service->buildRoomTimetableMatrix($series);

        $this->assertSame(['2026-05-10', '2026-05-11'], $matrix['dates']->all());
        $this->assertSame(['Hall A', 'Hall B', 'Hall C'], $matrix['resource_rows']->pluck('label')->all());
        $this->assertCount(1, $matrix['cells']['Hall A']['2026-05-10']);
        $this->assertSame('Group A', $matrix['cells']['Hall A']['2026-05-10']->first()['group']);
        $this->assertCount(0, $matrix['cells']['Hall A']['2026-05-11']);
        $this->assertCount(1, $matrix['cells']['Hall C']['2026-05-11']);
    }

    public function test_teacher_timetable_matrix_groups_teacher_duties_by_teacher_then_date(): void
    {
        [$term, $gradeSubject] = $this->createAcademicContext();
        $series = $this->createSeries($term, InvigilationSeries::POLICY_ANY_TEACHER);
        $teacherAlpha = $this->createTeacher('Teacher', 'Alpha');
        $teacherBravo = $this->createTeacher('Teacher', 'Bravo');

        $firstRoom = $this->createManualRoom($series, $gradeSubject, $this->createVenue('Hall A'), '2026-05-10', '08:00', '09:00', 'Group A');
        $secondRoom = $this->createManualRoom($series, $gradeSubject, $this->createVenue('Hall B'), '2026-05-10', '08:00', '09:00', 'Group B');
        $thirdRoom = $this->createManualRoom($series, $gradeSubject, $this->createVenue('Hall C'), '2026-05-11', '10:00', '11:00', 'Group C');

        $firstRoom->assignments()->create([
            'user_id' => $teacherAlpha->id,
            'assignment_order' => 1,
            'assignment_source' => 'manual',
            'locked' => true,
        ]);

        $secondRoom->assignments()->create([
            'user_id' => $teacherBravo->id,
            'assignment_order' => 1,
            'assignment_source' => 'auto',
            'locked' => false,
        ]);

        $thirdRoom->assignments()->create([
            'user_id' => $teacherAlpha->id,
            'assignment_order' => 1,
            'assignment_source' => 'manual',
            'locked' => true,
        ]);

        $matrix = $this->service->buildTeacherTimetableMatrix($series);

        $this->assertSame(['2026-05-10', '2026-05-11'], $matrix['dates']->all());
        $this->assertSame(['Teacher Alpha', 'Teacher Bravo'], $matrix['resource_rows']->pluck('label')->all());
        $this->assertCount(1, $matrix['cells']['Teacher Alpha']['2026-05-10']);
        $this->assertSame('Hall A', $matrix['cells']['Teacher Alpha']['2026-05-10']->first()['venue']);
        $this->assertCount(1, $matrix['cells']['Teacher Alpha']['2026-05-11']);
        $this->assertCount(1, $matrix['cells']['Teacher Bravo']['2026-05-10']);
    }

    public function test_publish_succeeds_when_series_has_full_coverage(): void
    {
        [$term, $gradeSubject] = $this->createAcademicContext();
        $teacher = $this->createTeacher('Published', 'Teacher');
        $this->limitCandidatePoolTo([$teacher->id]);
        $series = $this->createSeries($term, InvigilationSeries::POLICY_ANY_TEACHER);
        $room = $this->createManualRoom(
            $series,
            $gradeSubject,
            $this->createVenue('Hall Publish', 10),
            '2026-05-15',
            '08:00',
            '09:00',
            'Manual Group'
        );

        $room->assignments()->create([
            'user_id' => $teacher->id,
            'assignment_order' => 1,
            'assignment_source' => 'manual',
            'locked' => true,
        ]);

        $metrics = $this->service->publish($series, $teacher->id);

        $this->assertSame(InvigilationSeries::STATUS_PUBLISHED, $series->fresh()->status);
        $this->assertNotNull($series->fresh()->published_at);
        $this->assertSame($teacher->id, $series->fresh()->published_by);
        $this->assertSame(1, $metrics['sessions']);
    }

    public function test_validate_room_payload_allows_candidate_count_above_venue_capacity(): void
    {
        [$term, $gradeSubject] = $this->createAcademicContext();
        $series = $this->createSeries($term, InvigilationSeries::POLICY_ANY_TEACHER);
        $session = $series->sessions()->create([
            'grade_subject_id' => $gradeSubject->id,
            'paper_label' => 'Paper 1',
            'exam_date' => '2026-05-16',
            'start_time' => '08:00',
            'end_time' => '09:00',
        ]);
        $venue = $this->createVenue('Small Hall', 10);

        $payload = $this->service->validateRoomPayload($session->load('series'), [
            'venue_id' => $venue->id,
            'source_type' => InvigilationSessionRoom::SOURCE_MANUAL,
            'group_label' => 'Large Cohort',
            'candidate_count' => 60,
            'required_invigilators' => 1,
        ]);

        $this->assertSame(60, $payload['candidate_count']);
        $this->assertSame($venue->id, $payload['venue_id']);
    }

    public function test_unpublish_succeeds_only_from_published_and_clears_publish_metadata(): void
    {
        [$term] = $this->createAcademicContext();
        $series = $this->createSeries($term, InvigilationSeries::POLICY_ANY_TEACHER);

        try {
            $this->service->unpublish($series);
            $this->fail('Expected unpublish validation failure.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('unpublish', $exception->errors());
        }

        $series->update([
            'status' => InvigilationSeries::STATUS_PUBLISHED,
            'published_at' => now(),
            'published_by' => 99,
        ]);

        $metrics = $this->service->unpublish($series);

        $freshSeries = $series->fresh();

        $this->assertSame(InvigilationSeries::STATUS_DRAFT, $freshSeries->status);
        $this->assertNull($freshSeries->published_at);
        $this->assertNull($freshSeries->published_by);
        $this->assertSame(0, $metrics['sessions']);
    }

    private function createAcademicContext(bool $includeGrade = false): array
    {
        $term = Term::query()->updateOrCreate(
            ['term' => 1, 'year' => 2026],
            [
                'start_date' => '2026-01-01',
                'end_date' => '2026-12-31',
                'closed' => false,
            ]
        );

        $grade = Grade::query()->create([
            'name' => 'Form 1',
            'sequence' => 1,
            'promotion' => 'Form 2',
            'description' => 'Junior grade',
            'level' => SchoolSetup::LEVEL_JUNIOR,
            'active' => true,
            'term_id' => $term->id,
            'year' => $term->year,
        ]);

        $subject = Subject::query()->create([
            'name' => 'Mathematics',
            'abbrev' => 'MATH',
            'canonical_key' => 'math_' . uniqid(),
            'level' => SchoolSetup::LEVEL_JUNIOR,
            'components' => false,
        ]);

        $departmentId = DB::table('departments')->where('name', 'Academic')->value('id');
        if (!$departmentId) {
            $departmentId = DB::table('departments')->insertGetId([
                'name' => 'Academic',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('grade_subject')->updateOrInsert(
            [
                'term_id' => $term->id,
                'grade_id' => $grade->id,
                'subject_id' => $subject->id,
            ],
            [
                'department_id' => $departmentId,
                'year' => $term->year,
                'sequence' => 1,
                'type' => 'core',
                'mandatory' => true,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ]
        );

        $gradeSubject = GradeSubject::query()
            ->where('term_id', $term->id)
            ->where('grade_id', $grade->id)
            ->where('subject_id', $subject->id)
            ->firstOrFail();

        return $includeGrade
            ? [$term, $gradeSubject, $grade]
            : [$term, $gradeSubject];
    }

    private function createTeacher(string $firstname, string $lastname): User
    {
        return User::withoutEvents(fn () => User::query()->create([
            'firstname' => $firstname,
            'lastname' => $lastname,
            'email' => strtolower($firstname) . '.' . strtolower($lastname) . '.' . uniqid() . '@example.com',
            'password' => 'secret',
            'area_of_work' => 'Teaching',
            'status' => 'Current',
            'position' => 'Teacher',
            'year' => 2026,
        ]));
    }

    private function limitCandidatePoolTo(array $userIds): void
    {
        User::query()
            ->where('area_of_work', 'Teaching')
            ->where('status', 'Current')
            ->whereNotIn('id', $userIds)
            ->update(['status' => 'Inactive']);
    }

    private function createVenue(string $name, int $capacity = 40): Venue
    {
        return Venue::query()->create([
            'name' => $name,
            'type' => 'Hall',
            'capacity' => $capacity,
        ]);
    }

    private function createSeries(
        Term $term,
        string $eligibilityPolicy,
        string $timetablePolicy = InvigilationSeries::TIMETABLE_IGNORE
    ): InvigilationSeries {
        return InvigilationSeries::query()->create([
            'name' => 'Mock Series ' . uniqid(),
            'type' => InvigilationSeries::TYPE_MOCK,
            'term_id' => $term->id,
            'status' => InvigilationSeries::STATUS_DRAFT,
            'eligibility_policy' => $eligibilityPolicy,
            'timetable_conflict_policy' => $timetablePolicy,
            'balancing_policy' => 'balanced',
            'default_required_invigilators' => 1,
        ]);
    }

    private function createManualRoom(
        InvigilationSeries $series,
        GradeSubject $gradeSubject,
        Venue $venue,
        string $examDate,
        string $startTime,
        string $endTime,
        string $groupLabel,
        int $candidateCount = 20,
        ?int $dayOfCycle = null
    ): InvigilationSessionRoom {
        $session = $series->sessions()->create([
            'grade_subject_id' => $gradeSubject->id,
            'paper_label' => 'Paper 1',
            'exam_date' => $examDate,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'day_of_cycle' => $dayOfCycle,
        ]);

        return $session->rooms()->create([
            'venue_id' => $venue->id,
            'source_type' => InvigilationSessionRoom::SOURCE_MANUAL,
            'group_label' => $groupLabel,
            'candidate_count' => $candidateCount,
            'required_invigilators' => 1,
        ]);
    }
}
