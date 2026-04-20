<?php

namespace Tests\Feature\Activities;

use App\Http\Middleware\EnsureProfileComplete;
use App\Models\Activities\Activity;
use App\Models\Activities\ActivityEvent;
use App\Models\Activities\ActivityResult;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsActivitiesRosterFixtures;
use Tests\Concerns\EnsuresActivitiesPhaseOneSchema;
use Tests\TestCase;

class ActivityHouseLinkTest extends TestCase
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

    public function test_house_linked_results_do_not_change_house_membership(): void
    {
        $admin = $this->createActivityUser('activities-house-admin@example.com', ['Activities Admin']);
        $assistant = $this->createActivityUser('activities-house-assistant@example.com', ['Teacher']);
        $term = $this->createActivityTerm(2026, 3);
        $grade = $this->createGradeForTerm($term, 'F3');
        $klass = $this->createKlassForTerm($term, $grade, $admin, 'F3 Emerald');
        $houseOne = $this->createHouseForTerm($term, $admin, $assistant, 'Tawana');
        $houseTwo = $this->createHouseForTerm($term, $admin, $assistant, 'Ngwato');
        $activity = $this->createActivityRecord($term, $admin, [
            'status' => Activity::STATUS_ACTIVE,
            'allow_house_linkage' => true,
        ]);

        $student = $this->createStudentForActivity($term, $grade, $klass, $houseOne, null, [
            'first_name' => 'Boitumelo',
            'last_name' => 'Kelebile',
        ]);

        $houseMembershipBefore = DB::table('student_house')
            ->where('student_id', $student->id)
            ->where('term_id', $term->id)
            ->orderBy('house_id')
            ->get()
            ->toArray();

        $event = $this->createActivityEventRecord($activity, $admin, [
            'title' => 'Inter-House Athletics',
            'house_linked' => true,
            'status' => ActivityEvent::STATUS_COMPLETED,
            'start_datetime' => '2026-06-14 09:00:00',
            'end_datetime' => '2026-06-14 13:00:00',
        ]);

        $this->actingAs($admin)
            ->put(route('activities.results.update', [$activity, $event]), [
                'scope' => ActivityResult::PARTICIPANT_HOUSE,
                'results' => [
                    $houseOne->id => [
                        'selected' => '1',
                        'result_label' => 'Champions',
                        'placement' => 1,
                        'points' => 12,
                        'award_name' => 'House Cup',
                        'score_value' => null,
                        'notes' => 'Won the overall house contest.',
                    ],
                    $houseTwo->id => [
                        'selected' => '1',
                        'result_label' => 'Runner Up',
                        'placement' => 2,
                        'points' => 9,
                        'award_name' => null,
                        'score_value' => null,
                        'notes' => 'Strong relay performance.',
                    ],
                ],
            ])
            ->assertRedirect(route('activities.results.edit', [$activity, $event]));

        $this->assertDatabaseHas('activity_results', [
            'activity_event_id' => $event->id,
            'participant_type' => ActivityResult::PARTICIPANT_HOUSE,
            'participant_id' => $houseOne->id,
            'placement' => 1,
            'points' => 12,
            'award_name' => 'House Cup',
        ]);

        $houseMembershipAfter = DB::table('student_house')
            ->where('student_id', $student->id)
            ->where('term_id', $term->id)
            ->orderBy('house_id')
            ->get()
            ->toArray();

        $this->assertEquals($houseMembershipBefore, $houseMembershipAfter);

        $this->actingAs($admin)
            ->get(route('activities.results.edit', [$activity, $event]))
            ->assertOk()
            ->assertSee('Tawana')
            ->assertSee('House Cup')
            ->assertSee('12 pts');
    }
}
