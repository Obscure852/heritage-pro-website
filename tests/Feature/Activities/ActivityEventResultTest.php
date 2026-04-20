<?php

namespace Tests\Feature\Activities;

use App\Http\Middleware\EnsureProfileComplete;
use App\Models\Activities\Activity;
use App\Models\Activities\ActivityEvent;
use App\Models\Activities\ActivityResult;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Session\Middleware\AuthenticateSession;
use Tests\Concerns\BuildsActivitiesRosterFixtures;
use Tests\Concerns\EnsuresActivitiesPhaseOneSchema;
use Tests\TestCase;

class ActivityEventResultTest extends TestCase
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

    public function test_completed_event_can_capture_student_results_and_display_points_and_awards(): void
    {
        $admin = $this->createActivityUser('activities-events-admin@example.com', ['Activities Admin']);
        $assistant = $this->createActivityUser('activities-events-assistant@example.com', ['Teacher']);
        $term = $this->createActivityTerm(2026, 2);
        $grade = $this->createGradeForTerm($term, 'F2');
        $klass = $this->createKlassForTerm($term, $grade, $admin, 'F2 Amber');
        $house = $this->createHouseForTerm($term, $admin, $assistant, 'Bamangwato');
        $activity = $this->createActivityRecord($term, $admin, [
            'status' => Activity::STATUS_ACTIVE,
            'allow_house_linkage' => true,
        ]);
        $student = $this->createStudentForActivity($term, $grade, $klass, $house, null, [
            'first_name' => 'Kago',
            'last_name' => 'Molefi',
        ]);
        $this->createActivityEnrollmentRecord($activity, $student, $admin, [
            'joined_at' => '2026-04-01 08:00:00',
        ]);

        $this->actingAs($admin)
            ->get(route('activities.events.index', $activity))
            ->assertOk()
            ->assertSee('Events and Results')
            ->assertSee('Create Event');

        $this->actingAs($admin)
            ->post(route('activities.events.store', $activity), [
                'title' => 'District Chess Finals',
                'event_type' => ActivityEvent::TYPE_COMPETITION,
                'status' => ActivityEvent::STATUS_SCHEDULED,
                'start_date' => '2026-04-10',
                'start_time' => '14:00',
                'end_date' => '2026-04-10',
                'end_time' => '17:00',
                'location' => 'Town Hall',
                'opponent_or_partner_name' => 'District Schools Board',
                'house_linked' => '0',
                'publish_to_calendar' => '0',
                'description' => 'Regional final match day.',
            ])
            ->assertRedirect(route('activities.events.index', $activity));

        $event = ActivityEvent::query()->where('activity_id', $activity->id)->firstOrFail();

        $this->actingAs($admin)
            ->put(route('activities.results.update', [$activity, $event]), [
                'scope' => ActivityResult::PARTICIPANT_STUDENT,
                'results' => [
                    $student->id => [
                        'selected' => '1',
                        'result_label' => 'Winner',
                        'placement' => 1,
                        'points' => 3,
                        'award_name' => 'Gold Medal',
                        'score_value' => '5.00',
                        'notes' => 'Won the final board cleanly.',
                    ],
                ],
            ])
            ->assertRedirect(route('activities.results.edit', [$activity, $event]))
            ->assertSessionHas('error');

        $this->actingAs($admin)
            ->patch(route('activities.events.update', [$activity, $event]), [
                'title' => 'District Chess Finals',
                'event_type' => ActivityEvent::TYPE_COMPETITION,
                'status' => ActivityEvent::STATUS_COMPLETED,
                'start_date' => '2026-04-10',
                'start_time' => '14:00',
                'end_date' => '2026-04-10',
                'end_time' => '17:00',
                'location' => 'Town Hall',
                'opponent_or_partner_name' => 'District Schools Board',
                'house_linked' => '0',
                'publish_to_calendar' => '0',
                'description' => 'Regional final match day.',
            ])
            ->assertRedirect(route('activities.events.index', $activity));

        $this->actingAs($admin)
            ->put(route('activities.results.update', [$activity, $event]), [
                'scope' => ActivityResult::PARTICIPANT_STUDENT,
                'results' => [
                    $student->id => [
                        'selected' => '1',
                        'result_label' => 'Winner',
                        'placement' => 1,
                        'points' => 3,
                        'award_name' => 'Gold Medal',
                        'score_value' => '5.00',
                        'notes' => 'Won the final board cleanly.',
                    ],
                ],
            ])
            ->assertRedirect(route('activities.results.edit', [$activity, $event]));

        $this->assertDatabaseHas('activity_results', [
            'activity_event_id' => $event->id,
            'participant_type' => ActivityResult::PARTICIPANT_STUDENT,
            'participant_id' => $student->id,
            'placement' => 1,
            'points' => 3,
            'award_name' => 'Gold Medal',
            'result_label' => 'Winner',
        ]);

        $this->actingAs($admin)
            ->get(route('activities.results.edit', [$activity, $event]))
            ->assertOk()
            ->assertSee('Gold Medal')
            ->assertSee('3 pts')
            ->assertSee('Winner')
            ->assertSee('Kago Molefi');

        $this->actingAs($admin)
            ->get(route('activities.events.index', $activity))
            ->assertOk()
            ->assertSee('1 award(s)')
            ->assertSee('3 point(s)');
    }
}
