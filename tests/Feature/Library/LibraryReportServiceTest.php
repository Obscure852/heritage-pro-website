<?php

namespace Tests\Feature\Library;

use App\Models\Book;
use App\Models\Copy;
use App\Models\Library\LibraryFine;
use App\Models\Library\LibraryTransaction;
use App\Services\Library\LibraryReportService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class LibraryReportServiceTest extends TestCase {
    use DatabaseTransactions, LibraryTestHelper;

    protected LibraryReportService $service;

    protected function setUp(): void {
        parent::setUp();
        $this->seedDefaultLibrarySettings();
        $this->service = app(LibraryReportService::class);
    }

    public function test_circulation_report_by_date_range(): void {
        $borrower = $this->createStaffBorrower();
        $copy = Copy::factory()->create();

        LibraryTransaction::factory()->create([
            'copy_id' => $copy->id,
            'borrower_type' => 'user',
            'borrower_id' => $borrower->id,
            'checkout_date' => now()->subDays(5),
        ]);

        // Transaction outside range
        LibraryTransaction::factory()->create([
            'copy_id' => Copy::factory()->create()->id,
            'borrower_type' => 'user',
            'borrower_id' => $borrower->id,
            'checkout_date' => now()->subDays(30),
        ]);

        $report = $this->service->getCirculationReport(
            now()->subDays(7),
            now()
        );

        $this->assertCount(1, $report);
    }

    public function test_circulation_report_by_borrower_type(): void {
        $staff = $this->createStaffBorrower();
        $student = $this->createStudentBorrower();

        LibraryTransaction::factory()->create([
            'borrower_type' => 'user',
            'borrower_id' => $staff->id,
            'checkout_date' => today(),
        ]);

        LibraryTransaction::factory()->create([
            'borrower_type' => 'student',
            'borrower_id' => $student->id,
            'checkout_date' => today(),
        ]);

        $staffReport = $this->service->getCirculationReport(
            now()->subDays(1),
            now(),
            'user'
        );

        $this->assertCount(1, $staffReport);
    }

    public function test_overdue_report_current_snapshot(): void {
        $borrower = $this->createStaffBorrower();
        $this->createOverdueTransaction($borrower, 5);

        $report = $this->service->getOverdueReport();

        $this->assertCount(1, $report);
        $this->assertArrayHasKey('days_overdue', $report->first());
    }

    public function test_most_borrowed_report(): void {
        $borrower = $this->createStaffBorrower();
        $book = Book::factory()->create();
        $copy = Copy::factory()->create(['book_id' => $book->id]);

        LibraryTransaction::factory()->count(3)->create([
            'copy_id' => $copy->id,
            'borrower_type' => 'user',
            'borrower_id' => $borrower->id,
            'checkout_date' => today(),
        ]);

        $report = $this->service->getMostBorrowedReport(
            now()->subDays(7),
            now()
        );

        $this->assertGreaterThanOrEqual(1, $report->count());
        $this->assertEquals(3, $report->first()->checkout_count);
    }

    public function test_borrower_activity_aggregate(): void {
        $staff = $this->createStaffBorrower();
        $student = $this->createStudentBorrower();

        LibraryTransaction::factory()->create([
            'borrower_type' => 'user',
            'borrower_id' => $staff->id,
            'checkout_date' => today(),
        ]);

        LibraryTransaction::factory()->create([
            'borrower_type' => 'student',
            'borrower_id' => $student->id,
            'checkout_date' => today(),
        ]);

        $report = $this->service->getBorrowerActivityReport(
            now()->subDays(1),
            now()
        );

        $this->assertCount(2, $report);
    }

    public function test_borrower_activity_individual(): void {
        $borrower = $this->createStaffBorrower();

        LibraryTransaction::factory()->count(2)->create([
            'borrower_type' => 'user',
            'borrower_id' => $borrower->id,
            'checkout_date' => today(),
        ]);

        $report = $this->service->getIndividualBorrowerReport(
            'user',
            $borrower->id,
            now()->subDays(1),
            now()
        );

        $this->assertArrayHasKey('borrower_name', $report);
        $this->assertArrayHasKey('records', $report);
        $this->assertArrayHasKey('summary', $report);
        $this->assertCount(2, $report['records']);
    }

    public function test_collection_development_report(): void {
        $book = Book::factory()->create(['genre' => 'Science']);
        Copy::factory()->create(['book_id' => $book->id, 'status' => 'available']);
        Copy::factory()->create(['book_id' => $book->id, 'status' => 'checked_out']);

        $report = $this->service->getCollectionDevelopmentReport();

        $this->assertGreaterThanOrEqual(1, $report->count());

        $scienceRow = $report->firstWhere('genre', 'Science');
        $this->assertNotNull($scienceRow);
    }

    public function test_fine_collection_report(): void {
        $borrower = $this->createStaffBorrower();
        $transaction = LibraryTransaction::factory()->create([
            'borrower_type' => 'user',
            'borrower_id' => $borrower->id,
        ]);

        LibraryFine::factory()->create([
            'library_transaction_id' => $transaction->id,
            'borrower_type' => 'user',
            'borrower_id' => $borrower->id,
            'amount' => '20.00',
            'amount_paid' => '5.00',
            'amount_waived' => '3.00',
            'fine_date' => today(),
        ]);

        $report = $this->service->getFineCollectionReport(
            now()->subDays(1),
            now()
        );

        $this->assertArrayHasKey('records', $report);
        $this->assertArrayHasKey('summary', $report);
        $this->assertEquals('20.00', $report['summary']['total_assessed']);
        $this->assertEquals('5.00', $report['summary']['total_collected']);
        $this->assertEquals('3.00', $report['summary']['total_waived']);
    }

    public function test_fine_collection_uses_bcmath(): void {
        $borrower = $this->createStaffBorrower();

        // Create multiple fines with precise amounts
        for ($i = 0; $i < 3; $i++) {
            $transaction = LibraryTransaction::factory()->create([
                'borrower_type' => 'user',
                'borrower_id' => $borrower->id,
            ]);

            LibraryFine::factory()->create([
                'library_transaction_id' => $transaction->id,
                'borrower_type' => 'user',
                'borrower_id' => $borrower->id,
                'amount' => '10.33',
                'amount_paid' => '0.00',
                'amount_waived' => '0.00',
                'fine_date' => today(),
            ]);
        }

        $report = $this->service->getFineCollectionReport(
            now()->subDays(1),
            now()
        );

        // 10.33 * 3 = 30.99 (bcmath precise, not 30.989999...)
        $this->assertEquals('30.99', $report['summary']['total_assessed']);
    }

    public function test_report_filters_by_grade_class(): void {
        $student = $this->createStudentBorrower();

        // Assign student to a class via klass_student pivot
        $term = \App\Models\Term::currentOrLastActiveTerm();
        if ($term) {
            $grade = \App\Models\Grade::first();
            $klass = \App\Models\Klass::where('grade_id', $grade->id)->first();

            if ($klass) {
                $student->classes()->attach($klass->id, [
                    'term_id' => $term->id,
                    'grade_id' => $grade->id,
                ]);

                LibraryTransaction::factory()->create([
                    'borrower_type' => 'student',
                    'borrower_id' => $student->id,
                    'checkout_date' => today(),
                ]);

                $report = $this->service->getCirculationReport(
                    now()->subDays(1),
                    now(),
                    'student',
                    $grade->id
                );

                $this->assertGreaterThanOrEqual(1, $report->count());
            } else {
                $this->markTestSkipped('No Klass record found for grade/class filter test');
            }
        } else {
            $this->markTestSkipped('No active term found for grade/class filter test');
        }
    }
}
