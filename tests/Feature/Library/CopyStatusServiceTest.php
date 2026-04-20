<?php

namespace Tests\Feature\Library;

use App\Models\Copy;
use App\Models\Library\LibraryAuditLog;
use App\Services\Library\CopyStatusService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CopyStatusServiceTest extends TestCase {
    use DatabaseTransactions, LibraryTestHelper;

    protected CopyStatusService $service;

    protected function setUp(): void {
        parent::setUp();
        $this->service = app(CopyStatusService::class);
    }

    public function test_transition_changes_copy_status(): void {
        $copy = $this->createAvailableCopy();

        $result = $this->service->transition($copy, 'checked_out');

        $this->assertEquals('checked_out', $result->fresh()->status);
    }

    public function test_transition_to_same_status_throws(): void {
        $copy = $this->createAvailableCopy();

        $this->expectException(\InvalidArgumentException::class);

        $this->service->transition($copy, 'available');
    }

    public function test_transition_invalid_throws(): void {
        $copy = Copy::factory()->checkedOut()->create();

        $this->expectException(\InvalidArgumentException::class);

        $this->service->transition($copy, 'on_hold');
    }

    public function test_available_to_checked_out(): void {
        $copy = $this->createAvailableCopy();

        $result = $this->service->transition($copy, 'checked_out');

        $this->assertEquals('checked_out', $result->fresh()->status);
    }

    public function test_checked_out_to_available(): void {
        $copy = Copy::factory()->checkedOut()->create();

        $result = $this->service->transition($copy, 'available');

        $this->assertEquals('available', $result->fresh()->status);
    }

    public function test_checked_out_to_lost(): void {
        $copy = Copy::factory()->checkedOut()->create();

        $result = $this->service->transition($copy, 'lost');

        $this->assertEquals('lost', $result->fresh()->status);
    }

    public function test_available_to_on_hold(): void {
        $copy = $this->createAvailableCopy();

        $result = $this->service->transition($copy, 'on_hold');

        $this->assertEquals('on_hold', $result->fresh()->status);
    }

    public function test_on_hold_to_available(): void {
        $copy = Copy::factory()->onHold()->create();

        $result = $this->service->transition($copy, 'available');

        $this->assertEquals('available', $result->fresh()->status);
    }

    public function test_on_hold_to_checked_out_blocked_by_checkout_guard(): void {
        // on_hold → checked_out is allowed by TRANSITIONS map but blocked
        // by canLibraryCheckout guard (status must be 'available')
        $copy = Copy::factory()->onHold()->create();

        $this->expectException(\RuntimeException::class);

        $this->service->transition($copy, 'checked_out');
    }

    public function test_lost_to_available(): void {
        $copy = Copy::factory()->lost()->create();

        $result = $this->service->transition($copy, 'available');

        $this->assertEquals('available', $result->fresh()->status);
    }

    public function test_available_to_in_repair(): void {
        $copy = $this->createAvailableCopy();

        $result = $this->service->transition($copy, 'in_repair');

        $this->assertEquals('in_repair', $result->fresh()->status);
    }

    public function test_transition_creates_audit_log(): void {
        $copy = $this->createAvailableCopy();

        $this->service->transition($copy, 'checked_out');

        $this->assertDatabaseHas('library_audit_logs', [
            'auditable_type' => Copy::class,
            'auditable_id' => $copy->id,
            'action' => 'status_change',
        ]);
    }
}
