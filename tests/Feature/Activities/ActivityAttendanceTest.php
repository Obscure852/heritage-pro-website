<?php

namespace Tests\Feature\Activities;

use App\Http\Middleware\EnsureProfileComplete;
use App\Models\Activities\ActivityEnrollment;
use App\Models\Activities\ActivitySession;
use App\Models\Activities\ActivitySessionAttendance;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Session\Middleware\AuthenticateSession;
use Tests\Concerns\BuildsActivitiesRosterFixtures;
use Tests\Concerns\EnsuresActivitiesPhaseOneSchema;
use Tests\TestCase;

class ActivityAttendanceTest extends TestCase
{
    use DatabaseTransactions;
    use BuildsActivitiesRosterFixtures;
    use EnsuresActivitiesPhaseOneSchema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(EnsureProfileComplete::class);
        $this->withoutMiddleware(AuthenticateSession::class);
        $this->withoutMiddleware(\App\Http\Middleware\Authenticate::class);
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $this->ensureActivitiesPhaseOneSchema();
        $this->seedActivitiesSchoolSetup();
    }

    public function test_attendance_page_only_lists_students_enrolled_on_the_session_date_and_can_be_finalized(): void
    {
        $admin = $this->createActivityUser('activities-attendance-admin@example.com', ['Activities Admin']);
        $assistant = $this->createActivityUser('activities-attendance-assistant@example.com', ['Teacher']);
        $term = $this->createActivityTerm(2026, 2);
        $grade = $this->createGradeForTerm($term, 'F2');
        $klass = $this->createKlassForTerm($term, $grade, $admin, 'F2 Blue');
        $house = $this->createHouseForTerm($term, $admin, $assistant, 'Tawana');
        $activity = $this->createActivityRecord($term, $admin, [
            'status' => \App\Models\Activities\Activity::STATUS_ACTIVE,
        ]);
        $session = $this->createActivitySessionRecord($activity, null, [
            'session_date' => '2026-04-10',
            'start_datetime' => '2026-04-10 15:00:00',
            'end_datetime' => '2026-04-10 16:00:00',
        ]);

        $currentStudent = $this->createStudentForActivity($term, $grade, $klass, $house, null, [
            'first_name' => 'Neo',
            'last_name' => 'Current',
        ]);
        $historicalStudent = $this->createStudentForActivity($term, $grade, $klass, $house, null, [
            'first_name' => 'Mpho',
            'last_name' => 'Historical',
        ]);
        $leftBeforeStudent = $this->createStudentForActivity($term, $grade, $klass, $house, null, [
            'first_name' => 'Lebo',
            'last_name' => 'Left',
        ]);

        $currentEnrollment = $this->createActivityEnrollmentRecord($activity, $currentStudent, $admin, [
            'joined_at' => '2026-03-01 08:00:00',
        ]);
        $historicalEnrollment = $this->createActivityEnrollmentRecord($activity, $historicalStudent, $admin, [
            'status' => ActivityEnrollment::STATUS_WITHDRAWN,
            'joined_at' => '2026-03-01 08:00:00',
            'left_at' => '2026-04-12 10:00:00',
            'left_by' => $admin->id,
            'exit_reason' => 'Transferred after event.',
        ]);
        $this->createActivityEnrollmentRecord($activity, $leftBeforeStudent, $admin, [
            'status' => ActivityEnrollment::STATUS_WITHDRAWN,
            'joined_at' => '2026-03-01 08:00:00',
            'left_at' => '2026-04-08 10:00:00',
            'left_by' => $admin->id,
            'exit_reason' => 'Left before session.',
        ]);

        $this->actingAs($admin)
            ->get(route('activities.attendance.edit', [$activity, $session]))
            ->assertOk()
            ->assertSee('Neo Current')
            ->assertSee('Mpho Historical')
            ->assertDontSee('Lebo Left');

        $this->actingAs($admin)
            ->put(route('activities.attendance.update', [$activity, $session]), [
                'attendance' => [
                    $currentEnrollment->id => [
                        'status' => ActivitySessionAttendance::STATUS_PRESENT,
                        'remarks' => 'On time.',
                    ],
                    $historicalEnrollment->id => [
                        'status' => ActivitySessionAttendance::STATUS_LATE,
                        'remarks' => 'Arrived after assembly.',
                    ],
                ],
            ])
            ->assertRedirect(route('activities.attendance.edit', [$activity, $session]));

        $this->assertDatabaseHas('activity_session_attendance', [
            'activity_session_id' => $session->id,
            'activity_enrollment_id' => $currentEnrollment->id,
            'status' => ActivitySessionAttendance::STATUS_PRESENT,
        ]);

        $this->actingAs($admin)
            ->post(route('activities.attendance.finalize', [$activity, $session]))
            ->assertRedirect(route('activities.attendance.edit', [$activity, $session]));

        $this->assertDatabaseHas('activity_sessions', [
            'id' => $session->id,
            'attendance_locked' => true,
            'status' => ActivitySession::STATUS_COMPLETED,
        ]);

        $this->actingAs($admin)
            ->put(route('activities.attendance.update', [$activity, $session]), [
                'attendance' => [
                    $currentEnrollment->id => [
                        'status' => ActivitySessionAttendance::STATUS_ABSENT,
                        'remarks' => 'Should be blocked.',
                    ],
                ],
            ])
            ->assertRedirect(route('activities.attendance.edit', [$activity, $session]))
            ->assertSessionHas('error');
    }

    public function test_assigned_activities_staff_can_save_attendance_but_cannot_reopen_locked_session(): void
    {
        $admin = $this->createActivityUser('activities-attendance-admin-two@example.com', ['Activities Admin']);
        $staffUser = $this->createActivityUser('activities-attendance-staff@example.com', ['Activities Staff']);
        $assistant = $this->createActivityUser('activities-attendance-helper@example.com', ['Teacher']);
        $term = $this->createActivityTerm(2026, 3);
        $grade = $this->createGradeForTerm($term, 'F3');
        $klass = $this->createKlassForTerm($term, $grade, $admin, 'F3 Gold');
        $house = $this->createHouseForTerm($term, $admin, $assistant, 'Khama');
        $activity = $this->createActivityRecord($term, $admin, [
            'status' => \App\Models\Activities\Activity::STATUS_ACTIVE,
        ]);
        $session = $this->createActivitySessionRecord($activity, null, [
            'session_date' => '2026-06-15',
            'start_datetime' => '2026-06-15 10:00:00',
            'end_datetime' => '2026-06-15 11:00:00',
        ]);
        $student = $this->createStudentForActivity($term, $grade, $klass, $house, null, [
            'first_name' => 'Tebogo',
            'last_name' => 'Raletobana',
        ]);
        $enrollment = $this->createActivityEnrollmentRecord($activity, $student, $admin, [
            'joined_at' => '2026-05-01 08:00:00',
        ]);

        $this->assignPrimaryCoordinator($activity, $staffUser);

        $this->actingAs($staffUser)
            ->get(route('activities.attendance.edit', [$activity, $session]))
            ->assertOk()
            ->assertSee('Tebogo Raletobana');

        $this->actingAs($staffUser)
            ->put(route('activities.attendance.update', [$activity, $session]), [
                'attendance' => [
                    $enrollment->id => [
                        'status' => ActivitySessionAttendance::STATUS_PRESENT,
                        'remarks' => 'Coached attendance entry.',
                    ],
                ],
            ])
            ->assertRedirect(route('activities.attendance.edit', [$activity, $session]));

        $this->actingAs($staffUser)
            ->post(route('activities.attendance.finalize', [$activity, $session]))
            ->assertRedirect(route('activities.attendance.edit', [$activity, $session]));

        $this->actingAs($staffUser)
            ->post(route('activities.attendance.reopen', [$activity, $session]))
            ->assertForbidden();
    }
}
