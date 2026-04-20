<?php

namespace Tests\Feature\Attendance;

use App\Models\Grade;
use App\Models\Klass;
use App\Models\Role;
use App\Models\Sponsor;
use App\Models\Student;
use App\Models\Term;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceRegisterTermBoundaryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware();
    }

    public function test_first_page_uses_term_start_for_visible_range_and_disables_previous_navigation(): void
    {
        $setup = $this->createAttendanceContext('2026-01-21', '2026-03-27');
        $requestedWeekStart = Carbon::parse('2026-01-19');

        $jsonResponse = $this->actingAs($setup['admin'])
            ->withSession(['selected_term_id' => $setup['term']->id])
            ->getJson($this->classListUrl($setup['klass'], $setup['term'], $requestedWeekStart, true));

        $jsonResponse->assertOk()
            ->assertJsonPath('visibleStartDate', '2026-01-21')
            ->assertJsonPath('canGoPrevious', false)
            ->assertJsonPath('newWeekStart', '2026-01-19');

        $htmlResponse = $this->actingAs($setup['admin'])
            ->withSession(['selected_term_id' => $setup['term']->id])
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->get($this->classListUrl($setup['klass'], $setup['term'], $requestedWeekStart));

        $htmlResponse->assertOk();

        $content = $htmlResponse->getContent();
        $normalizedContent = preg_replace('/\s+/', ' ', $content) ?? $content;

        $this->assertStringContainsString('Jan 21 - Jan 30, 2026', $normalizedContent);
        $this->assertStringNotContainsString('Jan 19 -', $normalizedContent);
        $this->assertMatchesRegularExpression('/id="prevWeek"[^>]*disabled/', $content);
        $this->assertStringContainsString('class="week-nav-btn prev disabled"', $content);
    }

    public function test_last_page_uses_term_end_for_visible_range_and_disables_next_navigation(): void
    {
        $setup = $this->createAttendanceContext('2026-01-21', '2026-04-15');
        $requestedWeekStart = Carbon::parse('2026-04-15');

        $response = $this->actingAs($setup['admin'])
            ->withSession(['selected_term_id' => $setup['term']->id])
            ->getJson($this->classListUrl($setup['klass'], $setup['term'], $requestedWeekStart, true));

        $response->assertOk()
            ->assertJsonPath('visibleEndDate', '2026-04-15')
            ->assertJsonPath('canGoNext', false)
            ->assertJsonPath('newWeekStart', '2026-04-13');
    }

    public function test_navigate_week_advances_by_one_calendar_week_inside_term(): void
    {
        $setup = $this->createAttendanceContext('2026-01-21', '2026-04-15');

        $response = $this->actingAs($setup['admin'])
            ->withSession([
                'class_id' => $setup['klass']->id,
                'term_id' => $setup['term']->id,
                'selected_term_id' => $setup['term']->id,
            ])
            ->post(route('attendance.navigate-week'), [
                'currentWeekStart' => '2026-02-09',
                'direction' => 1,
            ]);

        $response->assertOk()
            ->assertJsonPath('newWeekStart', '2026-02-16')
            ->assertJsonPath('visibleStartDate', '2026-02-16')
            ->assertJsonPath('canGoPrevious', true)
            ->assertJsonPath('canGoNext', true);
    }

    public function test_short_terms_render_only_visible_school_days_without_hardcoded_ten_day_assumptions(): void
    {
        $setup = $this->createAttendanceContext('2026-01-22', '2026-01-27');
        $requestedWeekStart = Carbon::parse('2026-01-22');

        $response = $this->actingAs($setup['admin'])
            ->withSession(['selected_term_id' => $setup['term']->id])
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->get($this->classListUrl($setup['klass'], $setup['term'], $requestedWeekStart));

        $response->assertOk();

        $content = $response->getContent();

        $this->assertSame(4, substr_count($content, '<th class="attendance-header day-header'));
        $this->assertSame(1, substr_count($content, '<th class="week-divider"></th>'));
        $this->assertSame(4, substr_count($content, 'class="attendance-input day-'));
    }

    private function classListUrl(Klass $klass, Term $term, Carbon $weekStart, bool $asJson = false): string
    {
        $url = route('attendance.class-list', [
            'classId' => $klass->id,
            'termId' => $term->id,
            'weekStart' => $weekStart->toDateString(),
        ]);

        if ($asJson) {
            $url .= '?is_ajax=1';
        }

        return $url;
    }

    private function createAttendanceContext(string $termStart, string $termEnd): array
    {
        $termStartDate = Carbon::parse($termStart);
        $termEndDate = Carbon::parse($termEnd);

        $admin = User::factory()->create();
        $administratorRole = Role::query()->firstOrCreate(
            ['name' => 'Administrator'],
            ['description' => 'Administrator']
        );
        $admin->roles()->syncWithoutDetaching([$administratorRole->id]);

        $teacher = User::factory()->create();

        $term = Term::query()->updateOrCreate(
            [
                'term' => 1,
                'year' => (int) $termStartDate->format('Y'),
            ],
            [
                'start_date' => $termStartDate->toDateString(),
                'end_date' => $termEndDate->toDateString(),
                'closed' => false,
                'extension_days' => 0,
            ]
        );

        $grade = Grade::create([
            'sequence' => 1,
            'name' => 'Standard 1',
            'promotion' => 'Standard 2',
            'description' => 'Standard 1',
            'level' => 'Primary',
            'active' => true,
            'term_id' => $term->id,
            'year' => $term->year,
        ]);

        $klass = Klass::create([
            'name' => 'Blue',
            'user_id' => $teacher->id,
            'term_id' => $term->id,
            'grade_id' => $grade->id,
            'year' => $term->year,
            'active' => true,
        ]);

        $sponsor = Sponsor::create([
            'connect_id' => random_int(1000, 9999),
            'title' => 'Mr.',
            'first_name' => 'Parent',
            'last_name' => 'Guardian',
            'gender' => 'M',
            'status' => 'Current',
            'id_number' => (string) random_int(10000000, 99999999),
            'last_updated_by' => 'Administrator',
        ]);
        $student = Student::factory()->create([
            'connect_id' => $sponsor->id,
            'sponsor_id' => $sponsor->id,
            'year' => $term->year,
        ]);

        $klass->students()->attach($student->id, [
            'term_id' => $term->id,
            'grade_id' => $grade->id,
            'year' => $term->year,
            'active' => true,
        ]);

        return compact('admin', 'teacher', 'term', 'grade', 'klass', 'student');
    }
}
