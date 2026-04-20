<?php

namespace Tests\Unit\Activities;

use App\Models\Activities\ActivityEnrollment;
use App\Models\Activities\ActivitySessionAttendance;
use App\Services\Activities\ActivityAttendanceService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Validation\ValidationException;
use Tests\Concerns\BuildsActivitiesRosterFixtures;
use Tests\Concerns\EnsuresActivitiesPhaseOneSchema;
use Tests\TestCase;

class ActivityAttendanceServiceTest extends TestCase
{
    use DatabaseTransactions;
    use BuildsActivitiesRosterFixtures;
    use EnsuresActivitiesPhaseOneSchema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureActivitiesPhaseOneSchema();
        $this->seedActivitiesSchoolSetup();
    }

    public function test_finalize_requires_every_eligible_student_to_have_attendance(): void
    {
        $admin = $this->createActivityUser('activities-attendance-service-admin@example.com', ['Activities Admin']);
        $assistant = $this->createActivityUser('activities-attendance-service-assistant@example.com', ['Teacher']);
        $term = $this->createActivityTerm(2026, 1);
        $grade = $this->createGradeForTerm($term);
        $klass = $this->createKlassForTerm($term, $grade, $admin);
        $house = $this->createHouseForTerm($term, $admin, $assistant);
        $activity = $this->createActivityRecord($term, $admin);
        $session = $this->createActivitySessionRecord($activity, null, [
            'session_date' => '2026-02-02',
            'start_datetime' => '2026-02-02 15:00:00',
            'end_datetime' => '2026-02-02 16:00:00',
        ]);

        $studentOne = $this->createStudentForActivity($term, $grade, $klass, $house, null, [
            'first_name' => 'Student',
            'last_name' => 'One',
        ]);
        $studentTwo = $this->createStudentForActivity($term, $grade, $klass, $house, null, [
            'first_name' => 'Student',
            'last_name' => 'Two',
        ]);

        $enrollmentOne = $this->createActivityEnrollmentRecord($activity, $studentOne, $admin, [
            'joined_at' => '2026-01-15 08:00:00',
        ]);
        $this->createActivityEnrollmentRecord($activity, $studentTwo, $admin, [
            'joined_at' => '2026-01-15 08:00:00',
        ]);

        app(ActivityAttendanceService::class)->saveAttendance($activity, $session, [
            'attendance' => [
                $enrollmentOne->id => [
                    'status' => ActivitySessionAttendance::STATUS_PRESENT,
                    'remarks' => null,
                ],
            ],
        ], $admin);

        $this->expectException(ValidationException::class);

        app(ActivityAttendanceService::class)->finalizeAttendance($activity, $session, $admin);
    }

    public function test_eligible_enrollments_exclude_students_who_left_before_session_date(): void
    {
        $admin = $this->createActivityUser('activities-attendance-service-admin-two@example.com', ['Activities Admin']);
        $assistant = $this->createActivityUser('activities-attendance-service-helper@example.com', ['Teacher']);
        $term = $this->createActivityTerm(2026, 2);
        $grade = $this->createGradeForTerm($term, 'F2');
        $klass = $this->createKlassForTerm($term, $grade, $admin, 'F2 Silver');
        $house = $this->createHouseForTerm($term, $admin, $assistant, 'Bangwato');
        $activity = $this->createActivityRecord($term, $admin);
        $session = $this->createActivitySessionRecord($activity, null, [
            'session_date' => '2026-05-05',
            'start_datetime' => '2026-05-05 09:00:00',
            'end_datetime' => '2026-05-05 10:00:00',
        ]);

        $eligibleStudent = $this->createStudentForActivity($term, $grade, $klass, $house, null, [
            'first_name' => 'Eligible',
            'last_name' => 'Student',
        ]);
        $leftStudent = $this->createStudentForActivity($term, $grade, $klass, $house, null, [
            'first_name' => 'Left',
            'last_name' => 'Student',
        ]);

        $this->createActivityEnrollmentRecord($activity, $eligibleStudent, $admin, [
            'status' => ActivityEnrollment::STATUS_ACTIVE,
            'joined_at' => '2026-04-01 08:00:00',
        ]);
        $this->createActivityEnrollmentRecord($activity, $leftStudent, $admin, [
            'status' => ActivityEnrollment::STATUS_WITHDRAWN,
            'joined_at' => '2026-04-01 08:00:00',
            'left_at' => '2026-05-01 08:00:00',
            'left_by' => $admin->id,
        ]);

        $eligible = app(ActivityAttendanceService::class)->eligibleEnrollments($activity, $session);

        $this->assertSame(
            ['Eligible Student'],
            $eligible->map(fn ($enrollment) => $enrollment->student?->full_name)->all()
        );
    }
}
