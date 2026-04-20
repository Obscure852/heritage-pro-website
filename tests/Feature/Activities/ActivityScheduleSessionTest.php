<?php

namespace Tests\Feature\Activities;

use App\Http\Middleware\EnsureProfileComplete;
use App\Models\Activities\ActivitySchedule;
use App\Models\Activities\ActivitySession;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Session\Middleware\AuthenticateSession;
use Tests\Concerns\BuildsActivitiesRosterFixtures;
use Tests\Concerns\EnsuresActivitiesPhaseOneSchema;
use Tests\TestCase;

class ActivityScheduleSessionTest extends TestCase
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

    public function test_activity_admin_can_create_schedule_and_generate_sessions_without_duplicates(): void
    {
        $admin = $this->createActivityUser('activities-schedule-admin@example.com', ['Activities Admin']);
        $term = $this->createActivityTerm(2026, 1);
        $activity = $this->createActivityRecord($term, $admin, [
            'status' => \App\Models\Activities\Activity::STATUS_ACTIVE,
        ]);

        $this->actingAs($admin)
            ->post(route('activities.schedules.store', $activity), [
                'frequency' => ActivitySchedule::FREQUENCY_WEEKLY,
                'day_of_week' => 2,
                'start_time' => '15:00',
                'end_time' => '16:00',
                'start_date' => '2026-01-01',
                'end_date' => '2026-01-31',
                'location' => 'Science Lab',
                'notes' => 'Tuesday practice block.',
                'active' => '1',
            ])
            ->assertRedirect(route('activities.schedules.index', $activity));

        $schedule = ActivitySchedule::query()->where('activity_id', $activity->id)->firstOrFail();

        $this->actingAs($admin)
            ->post(route('activities.schedules.generate', [$activity, $schedule]), [
                'generate_from' => '2026-01-01',
                'generate_to' => '2026-01-31',
            ])
            ->assertRedirect(route('activities.schedules.index', $activity));

        $generatedCount = ActivitySession::query()
            ->where('activity_id', $activity->id)
            ->count();

        $this->assertSame(4, $generatedCount);

        $this->actingAs($admin)
            ->post(route('activities.schedules.generate', [$activity, $schedule]), [
                'generate_from' => '2026-01-01',
                'generate_to' => '2026-01-31',
            ])
            ->assertRedirect(route('activities.schedules.index', $activity));

        $this->assertSame(
            $generatedCount,
            ActivitySession::query()->where('activity_id', $activity->id)->count()
        );
    }

    public function test_manual_session_can_be_created_and_updated_from_schedule_workspace(): void
    {
        $admin = $this->createActivityUser('activities-session-admin@example.com', ['Activities Admin']);
        $term = $this->createActivityTerm(2026, 2);
        $activity = $this->createActivityRecord($term, $admin, [
            'status' => \App\Models\Activities\Activity::STATUS_ACTIVE,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('activities.schedules.index', $activity));

        $response->assertOk()
            ->assertSee('Schedules, Sessions, and Attendance')
            ->assertSee('Save Manual Session');

        $this->actingAs($admin)
            ->post(route('activities.sessions.store', $activity), [
                'session_type' => ActivitySession::TYPE_MANUAL,
                'session_date' => '2026-04-03',
                'start_time' => '14:00',
                'end_time' => '15:30',
                'location' => 'Main Hall',
                'status' => ActivitySession::STATUS_PLANNED,
                'notes' => 'Manual showcase rehearsal.',
            ])
            ->assertRedirect(route('activities.schedules.index', $activity));

        $session = ActivitySession::query()->where('activity_id', $activity->id)->firstOrFail();

        $this->actingAs($admin)
            ->patch(route('activities.sessions.update', [$activity, $session]), [
                'activity_schedule_id' => null,
                'session_type' => $session->session_type,
                'session_date' => '2026-04-04',
                'start_time' => '16:00',
                'end_time' => '17:00',
                'location' => 'Updated Hall',
                'status' => ActivitySession::STATUS_POSTPONED,
                'notes' => 'Moved by one day.',
            ])
            ->assertRedirect(route('activities.schedules.index', $activity));

        $this->assertDatabaseHas('activity_sessions', [
            'id' => $session->id,
            'status' => ActivitySession::STATUS_POSTPONED,
            'location' => 'Updated Hall',
            'session_date' => '2026-04-04',
        ]);
    }
}
