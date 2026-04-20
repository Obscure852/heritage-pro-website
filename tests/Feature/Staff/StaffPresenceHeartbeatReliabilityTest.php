<?php

namespace Tests\Feature\Staff;

use App\Exceptions\Handler;
use App\Http\Middleware\BlockNonAfricanCountries;
use App\Http\Middleware\EnsureProfileComplete;
use App\Http\Middleware\VerifyCsrfToken;
use App\Models\User;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Store;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class StaffPresenceHeartbeatReliabilityTest extends TestCase
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

        $this->seedMessagingSettings();
    }

    public function test_heartbeat_route_updates_presence_without_csrf_token(): void
    {
        $user = $this->createStaffUser([
            'firstname' => 'Heartbeat',
            'lastname' => 'Tester',
        ]);

        $this->useSessionId('heartbeat-session');

        $this->actingAs($user)
            ->withCookie(config('session.cookie'), 'heartbeat-session')
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->postJson(route('staff.messages.heartbeat'), [
                'last_path' => '/dashboard',
            ])
            ->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('staff_user_presence', [
            'session_id' => 'heartbeat-session',
            'user_id' => $user->id,
            'last_path' => '/dashboard',
        ]);
    }

    public function test_token_mismatch_returns_json_for_fetch_requests(): void
    {
        $request = Request::create('/staff/messages/conversations', 'POST', [], [], [], [
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
        ]);

        $response = app(Handler::class)->render(
            $request,
            new TokenMismatchException('CSRF token mismatch.')
        );

        $payload = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(419, $response->getStatusCode());
        $this->assertStringContainsString('application/json', (string) $response->headers->get('content-type'));
        $this->assertSame(419, data_get($payload, 'error.status'));
        $this->assertSame('Your session has expired. Please refresh and try again.', data_get($payload, 'error.message'));
    }

    public function test_two_authenticated_staff_sessions_see_each_other_as_online(): void
    {
        $firstUser = $this->createStaffUser([
            'firstname' => 'Martin',
            'lastname' => 'Admin',
        ]);
        $secondUser = $this->createStaffUser([
            'firstname' => 'Tech',
            'lastname' => 'Team',
        ]);

        $this->useSessionId('staff-session-one');
        $this->actingAs($firstUser)
            ->withCookie(config('session.cookie'), 'staff-session-one')
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->postJson(route('staff.messages.heartbeat'), [
                'last_path' => '/dashboard',
            ])
            ->assertOk();

        $this->useSessionId('staff-session-two');
        $this->actingAs($secondUser)
            ->withCookie(config('session.cookie'), 'staff-session-two')
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->postJson(route('staff.messages.heartbeat'), [
                'last_path' => '/notifications',
            ])
            ->assertOk();

        $this->assertDatabaseHas('staff_user_presence', [
            'session_id' => 'staff-session-one',
            'user_id' => $firstUser->id,
        ]);
        $this->assertDatabaseHas('staff_user_presence', [
            'session_id' => 'staff-session-two',
            'user_id' => $secondUser->id,
        ]);

        $firstLauncher = $this->actingAs($firstUser)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->getJson(route('staff.messages.launcher'))
            ->assertOk();

        $secondLauncher = $this->actingAs($secondUser)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->getJson(route('staff.messages.launcher'))
            ->assertOk();

        $this->assertSame(1, $firstLauncher->json('online_count'));
        $this->assertSame([$secondUser->id], $firstLauncher->json('users.*.id'));
        $this->assertSame(1, $secondLauncher->json('online_count'));
        $this->assertSame([$firstUser->id], $secondLauncher->json('users.*.id'));
    }

    public function test_only_heartbeat_endpoint_is_exempt_from_csrf_verification(): void
    {
        $middleware = $this->csrfMiddleware();

        $heartbeatResponse = $middleware->handle(
            $this->newCsrfRequest('/staff/messages/heartbeat'),
            fn (): Response => response('ok')
        );

        $this->assertSame(200, $heartbeatResponse->getStatusCode());

        foreach ([
            '/staff/messages/conversations',
            '/staff/messages/1/reply',
            '/staff/messages/1/archive',
            '/staff/messages/1/unarchive',
        ] as $path) {
            try {
                $middleware->handle(
                    $this->newCsrfRequest($path),
                    fn (): Response => response('ok')
                );

                $this->fail("Expected CSRF verification to reject [{$path}].");
            } catch (TokenMismatchException $exception) {
                $this->assertSame('CSRF token mismatch.', $exception->getMessage());
            }
        }
    }

    protected function csrfMiddleware(): VerifyCsrfToken
    {
        return new class($this->app, $this->app['encrypter']) extends VerifyCsrfToken {
            protected function runningUnitTests()
            {
                return false;
            }
        };
    }

    protected function newCsrfRequest(string $path): Request
    {
        $request = Request::create($path, 'POST', [], [], [], [
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
        ]);

        $session = new Store('testing', new ArraySessionHandler(120));
        $session->start();
        $request->setLaravelSession($session);

        return $request;
    }

    protected function useSessionId(string $sessionId): void
    {
        if ($this->app['session']->isStarted()) {
            $this->app['session']->flush();
            $this->app['session']->save();
        }

        $this->app['session']->setId($sessionId);
        $this->app['session']->start();
    }

    protected function createStaffUser(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'firstname' => 'Staff',
            'lastname' => 'User' . random_int(1000, 9999),
            'email' => 'staff' . random_int(1000, 9999) . '@example.com',
            'username' => 'staff' . random_int(1000, 9999),
            'avatar' => null,
            'active' => true,
            'password' => bcrypt('password'),
        ], $attributes));
    }

    protected function seedMessagingSettings(array $overrides = []): void
    {
        $settings = [
            'features.staff_direct_messages_enabled' => [
                'value' => '1',
                'category' => 'feature',
                'type' => 'boolean',
                'display_name' => 'Enable Staff Direct Messaging',
                'description' => 'Allow or block internal staff direct messaging system-wide.',
                'validation_rules' => 'required|boolean',
                'display_order' => 900,
            ],
            'features.staff_presence_launcher_enabled' => [
                'value' => '1',
                'category' => 'feature',
                'type' => 'boolean',
                'display_name' => 'Enable Online Staff Launcher',
                'description' => 'Show or hide the quiet online-staff launcher in the staff topbar.',
                'validation_rules' => 'required|boolean',
                'display_order' => 901,
            ],
            'internal_messaging.online_window_minutes' => [
                'value' => '2',
                'category' => 'internal_messaging',
                'type' => 'integer',
                'display_name' => 'Online Window (minutes)',
                'description' => 'How long a staff heartbeat remains valid before the user appears offline.',
                'validation_rules' => 'required|integer|min:1|max:60',
                'display_order' => 1,
            ],
            'internal_messaging.launcher_poll_seconds' => [
                'value' => '45',
                'category' => 'internal_messaging',
                'type' => 'integer',
                'display_name' => 'Launcher Poll Interval (seconds)',
                'description' => 'How often the quiet launcher refreshes presence and unread counts.',
                'validation_rules' => 'required|integer|min:15|max:300',
                'display_order' => 2,
            ],
            'internal_messaging.conversation_poll_seconds' => [
                'value' => '5',
                'category' => 'internal_messaging',
                'type' => 'integer',
                'display_name' => 'Conversation Poll Interval (seconds)',
                'description' => 'How often an open direct-message conversation checks for new messages.',
                'validation_rules' => 'required|integer|min:3|max:30',
                'display_order' => 3,
            ],
        ];

        foreach ($overrides as $key => $value) {
            if (isset($settings[$key])) {
                $settings[$key]['value'] = (string) $value;
            }
        }

        foreach ($settings as $key => $setting) {
            \DB::table('s_m_s_api_settings')->updateOrInsert(
                ['key' => $key],
                array_merge($setting, [
                    'key' => $key,
                    'is_editable' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        app(SettingsService::class)->refresh();
    }
}
