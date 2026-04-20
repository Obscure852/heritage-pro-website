<?php

namespace Tests\Feature\Auth;

use App\Http\Middleware\BlockNonAfricanCountries;
use App\Http\Middleware\EnsureProfileComplete;
use App\Models\Sponsor;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class IdleSessionTimeoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([
            BlockNonAfricanCountries::class,
            EnsureProfileComplete::class,
            AuthenticateSession::class,
        ]);

        $this->registerProtectedRoutes();
    }

    public function test_recent_activity_allows_authenticated_requests_for_all_session_guards(): void
    {
        foreach ($this->guardConfigurations() as $guard => $config) {
            $this->resetAuthState();
            $user = $this->authenticateForGuard($guard);
            $key = $this->idleSessionKey($guard);

            $this->actingAs($user, $guard)
                ->withSession([$key => now()->subMinutes(5)->timestamp])
                ->getJson($config['protected_url'])
                ->assertOk()
                ->assertJson([
                    'guard' => $guard,
                ]);
        }
    }

    public function test_idle_timeout_redirects_full_page_requests_to_the_correct_login_page(): void
    {
        foreach ($this->guardConfigurations() as $guard => $config) {
            $this->resetAuthState();
            $user = $this->authenticateForGuard($guard);
            $key = $this->idleSessionKey($guard);

            $this->actingAs($user, $guard)
                ->withSession([$key => now()->subMinutes(11)->timestamp])
                ->get($config['protected_url'])
                ->assertRedirect($config['login_url'])
                ->assertSessionHas('message', 'Your session expired due to inactivity. Please sign in again.');
        }
    }

    public function test_idle_timeout_returns_a_json_reason_for_all_session_guards(): void
    {
        foreach ($this->guardConfigurations() as $guard => $config) {
            $this->resetAuthState();
            $user = $this->authenticateForGuard($guard);
            $key = $this->idleSessionKey($guard);

            $this->actingAs($user, $guard)
                ->withSession([$key => now()->subMinutes(11)->timestamp])
                ->getJson($config['protected_url'])
                ->assertStatus(401)
                ->assertJsonPath('error.status', 401)
                ->assertJsonPath('error.reason', 'idle_timeout');
        }
    }

    public function test_activity_endpoint_refreshes_last_activity_for_all_session_guards(): void
    {
        foreach ($this->guardConfigurations() as $guard => $config) {
            $this->resetAuthState();
            $user = $this->authenticateForGuard($guard);
            $key = $this->idleSessionKey($guard);
            $staleTimestamp = now()->subMinutes(9)->timestamp;

            $this->actingAs($user, $guard)
                ->withSession([$key => $staleTimestamp])
                ->postJson($config['activity_url'])
                ->assertOk()
                ->assertJsonPath('success', true);

            $this->assertGreaterThan($staleTimestamp, (int) $this->app['session']->get($key));
        }
    }

    protected function registerProtectedRoutes(): void
    {
        Route::middleware(['web', 'auth'])
            ->get('/_test/idle/web', fn () => response()->json(['guard' => 'web']));

        Route::middleware(['web', 'auth:sponsor'])
            ->get('/_test/idle/sponsor', fn () => response()->json(['guard' => 'sponsor']));

        Route::middleware(['web', 'auth:student'])
            ->get('/_test/idle/student', fn () => response()->json(['guard' => 'student']));
    }

    protected function guardConfigurations(): array
    {
        return [
            'web' => [
                'protected_url' => '/_test/idle/web',
                'activity_url' => route('auth.activity'),
                'login_url' => route('login'),
            ],
            'sponsor' => [
                'protected_url' => '/_test/idle/sponsor',
                'activity_url' => route('sponsor.activity'),
                'login_url' => route('sponsor.login'),
            ],
            'student' => [
                'protected_url' => '/_test/idle/student',
                'activity_url' => route('student.activity'),
                'login_url' => route('student.login'),
            ],
        ];
    }

    protected function idleSessionKey(string $guard): string
    {
        return app(\App\Services\Auth\IdleSessionService::class)->sessionKey($guard);
    }

    protected function authenticateForGuard(string $guard): User|Sponsor|Student
    {
        return match ($guard) {
            'sponsor' => $this->createSponsor(),
            'student' => $this->createStudent(),
            default => User::factory()->create([
                'active' => true,
                'password' => bcrypt('password'),
            ]),
        };
    }

    protected function createSponsor(): Sponsor
    {
        return Sponsor::query()->create([
            'connect_id' => random_int(1000, 9999),
            'title' => 'Mr.',
            'first_name' => 'Sponsor',
            'last_name' => 'User' . random_int(1000, 9999),
            'email' => 'sponsor-' . uniqid() . '@example.com',
            'gender' => 'M',
            'date_of_birth' => '1970-01-01',
            'nationality' => 'Motswana',
            'relation' => 'Parent',
            'status' => 'Current',
            'id_number' => (string) random_int(10000000, 99999999),
            'phone' => '71234567',
            'profession' => 'Parent',
            'work_place' => 'Test Workplace',
            'telephone' => '3900000',
            'password' => bcrypt('password'),
            'last_updated_by' => 'Test Suite',
        ]);
    }

    protected function createStudent(): Student
    {
        $sponsor = $this->createSponsor();

        return Student::query()->create([
            'connect_id' => $sponsor->id,
            'sponsor_id' => $sponsor->id,
            'first_name' => 'Student',
            'last_name' => 'User' . random_int(1000, 9999),
            'gender' => 'M',
            'date_of_birth' => '2010-01-01',
            'email' => 'student-' . uniqid() . '@example.com',
            'nationality' => 'Motswana',
            'id_number' => (string) random_int(10000000, 99999999),
            'status' => 'Current',
            'credit' => 0,
            'parent_is_staff' => false,
            'year' => 2023,
            'password' => bcrypt('password'),
            'last_updated_by' => 'Test Suite',
        ]);
    }

    protected function resetAuthState(): void
    {
        if ($this->app['session']->isStarted()) {
            $this->app['session']->flush();
            $this->app['session']->save();
        }

        $this->app['auth']->forgetGuards();
    }
}
