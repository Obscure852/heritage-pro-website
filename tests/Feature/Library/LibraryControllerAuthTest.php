<?php

namespace Tests\Feature\Library;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class LibraryControllerAuthTest extends TestCase {
    use DatabaseTransactions, LibraryTestHelper;

    public function test_guest_cannot_access_library(): void {
        $response = $this->get(route('library.dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_librarian_can_access_dashboard(): void {
        $librarian = $this->createLibrarianUser();

        $response = $this->actingAs($librarian)->get(route('library.dashboard'));

        $response->assertStatus(200);
    }

    public function test_student_cannot_access_settings(): void {
        // Create a user without Librarian or Admin role (simulates a regular staff)
        $user = User::factory()->create();
        $staffRole = Role::firstOrCreate(['name' => 'Staff']);
        $user->roles()->attach($staffRole);

        $response = $this->actingAs($user)->get(route('library.settings.index'));

        $response->assertStatus(403);
    }

    public function test_admin_can_access_settings(): void {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->get(route('library.settings.index'));

        $response->assertStatus(200);
    }

    public function test_librarian_can_checkout(): void {
        $librarian = $this->createLibrarianUser();

        $response = $this->actingAs($librarian)->get(route('library.circulation.index'));

        $response->assertStatus(200);
    }

    public function test_staff_cannot_checkout(): void {
        $user = User::factory()->create();
        $staffRole = Role::firstOrCreate(['name' => 'Staff']);
        $user->roles()->attach($staffRole);

        $response = $this->actingAs($user)->get(route('library.circulation.index'));

        $response->assertStatus(403);
    }

    public function test_only_admin_can_waive_fines(): void {
        $librarian = $this->createLibrarianUser();

        // Librarian should NOT be able to waive fines (admin-only gate)
        $this->actingAs($librarian);
        $this->assertFalse($librarian->can('waive-library-fines'));

        $admin = $this->createAdminUser();
        $this->assertTrue($admin->can('waive-library-fines'));
    }

    public function test_only_admin_can_delete_records(): void {
        $librarian = $this->createLibrarianUser();
        $this->assertFalse($librarian->can('delete-library-records'));

        $admin = $this->createAdminUser();
        $this->assertTrue($admin->can('delete-library-records'));
    }

    public function test_librarian_can_view_catalog(): void {
        $librarian = $this->createLibrarianUser();

        $response = $this->actingAs($librarian)->get(route('library.catalog.index'));

        $response->assertStatus(200);
    }

    public function test_librarian_can_view_reports(): void {
        $librarian = $this->createLibrarianUser();

        $response = $this->actingAs($librarian)->get(route('library.reports.circulation'));

        $response->assertStatus(200);
    }

    public function test_librarian_can_start_inventory(): void {
        $librarian = $this->createLibrarianUser();

        $response = $this->actingAs($librarian)->get(route('library.inventory.index'));

        $response->assertStatus(200);
    }

    public function test_unauthorized_returns_403(): void {
        $user = User::factory()->create();
        // No roles at all

        $response = $this->actingAs($user)->get(route('library.dashboard'));

        $response->assertStatus(403);
    }
}
