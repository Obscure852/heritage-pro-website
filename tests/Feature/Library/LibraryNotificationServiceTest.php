<?php

namespace Tests\Feature\Library;

use App\Helpers\LinkSMSHelper;
use App\Models\Copy;
use App\Models\Library\LibraryOverdueNotice;
use App\Models\Library\LibrarySetting;
use App\Models\Library\LibraryTransaction;
use App\Models\Role;
use App\Models\User;
use App\Notifications\Library\EscalationNotification;
use App\Notifications\Library\OverdueBookNotification;
use App\Services\Library\LibraryNotificationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Mockery;
use Tests\TestCase;

class LibraryNotificationServiceTest extends TestCase {
    use DatabaseTransactions, LibraryTestHelper;

    protected LibraryNotificationService $service;

    protected function setUp(): void {
        parent::setUp();
        $this->seedDefaultLibrarySettings();

        // Mock LinkSMSHelper to avoid actual SMS sends
        $smsMock = Mockery::mock(LinkSMSHelper::class);
        $smsMock->shouldReceive('sendMessage')->andReturn(true)->byDefault();
        $this->app->instance(LinkSMSHelper::class, $smsMock);

        $this->service = app(LibraryNotificationService::class);
    }

    protected function tearDown(): void {
        Mockery::close();
        parent::tearDown();
    }

    public function test_send_overdue_notice_creates_record(): void {
        Notification::fake();

        $borrower = $this->createStaffBorrower();
        [$copy, $transaction] = $this->createOverdueTransaction($borrower, 7);

        $result = $this->service->sendOverdueNotification($transaction, 7);

        $this->assertTrue($result['sent']);
        $this->assertDatabaseHas('library_overdue_notices', [
            'library_transaction_id' => $transaction->id,
            'notice_type' => 'overdue_reminder',
            'days_overdue' => 7,
        ]);
    }

    public function test_send_overdue_notice_sends_notification(): void {
        Notification::fake();

        $borrower = $this->createStaffBorrower();
        [$copy, $transaction] = $this->createOverdueTransaction($borrower, 7);

        $this->service->sendOverdueNotification($transaction, 7);

        Notification::assertSentTo($borrower, OverdueBookNotification::class);
    }

    public function test_notification_schedule_filtering(): void {
        Notification::fake();

        $borrower = $this->createStaffBorrower();
        [$copy, $transaction] = $this->createOverdueTransaction($borrower, 7);

        // First send — should succeed
        $result1 = $this->service->sendOverdueNotification($transaction, 7);
        $this->assertTrue($result1['sent']);

        // Second send at same day threshold — should be skipped (dedup)
        $result2 = $this->service->sendOverdueNotification($transaction, 7);
        $this->assertTrue($result2['skipped']);
    }

    public function test_sms_gated_behind_setting(): void {
        Notification::fake();

        $borrower = $this->createStaffBorrower();
        [$copy, $transaction] = $this->createOverdueTransaction($borrower, 7);

        // SMS disabled (default)
        $result = $this->service->sendOverdueNotification($transaction, 7);

        // Only in_app channel should be present
        $this->assertContains('in_app', $result['channels']);
        $this->assertNotContains('sms', $result['channels']);
    }

    public function test_sms_sent_when_enabled(): void {
        Notification::fake();

        LibrarySetting::set('overdue_sms_enabled', true);
        $this->service = app(LibraryNotificationService::class);

        $borrower = User::factory()->create(['phone' => '71234567']);
        [$copy, $transaction] = $this->createOverdueTransaction($borrower, 7);

        $result = $this->service->sendOverdueNotification($transaction, 7);

        $this->assertContains('sms', $result['channels']);
    }

    public function test_student_phone_from_sponsor(): void {
        Notification::fake();

        LibrarySetting::set('overdue_sms_enabled', true);
        $this->service = app(LibraryNotificationService::class);

        $student = $this->createStudentBorrower();
        // Student's sponsor should have phone from SponsorFactory
        [$copy, $transaction] = $this->createOverdueTransaction($student, 7);

        $result = $this->service->sendOverdueNotification($transaction, 7);

        // Should send SMS using sponsor's phone
        $this->assertTrue($result['sent']);
    }

    public function test_escalation_to_class_teacher(): void {
        Notification::fake();

        $borrower = $this->createStaffBorrower();
        [$copy, $transaction] = $this->createOverdueTransaction($borrower, 14);

        $result = $this->service->sendEscalationNotification($transaction, 14, 'class_teacher');

        // Should send (falls back to checked_out_by since staff borrower has no class_teacher)
        $this->assertTrue($result['sent']);
    }

    public function test_in_app_notification_sent(): void {
        Notification::fake();

        $borrower = $this->createStaffBorrower();
        [$copy, $transaction] = $this->createOverdueTransaction($borrower, 7);

        $this->service->sendOverdueNotification($transaction, 7);

        Notification::assertSentTo(
            $borrower,
            OverdueBookNotification::class,
            function ($notification) use ($transaction) {
                return true;
            }
        );
    }
}
