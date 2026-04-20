<?php

namespace App\Services\Library;

use App\Models\Library\LibraryTransaction;
use App\Models\Library\LibraryFine;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LibraryReportService {
    /**
     * Resolve polymorphic borrower name with fallback chain.
     */
    protected function getBorrowerName($record): string {
        $borrower = $record->borrower;
        if (!$borrower) return '-';
        return $borrower->full_name ?? $borrower->name ?? '-';
    }

    /**
     * Map morph type to display label: 'student' -> 'Student', else 'Staff'.
     */
    protected function settingsKey(string $borrowerType): string {
        return $borrowerType === 'student' ? 'Student' : 'Staff';
    }

    /**
     * Filter transaction collections by grade/class (student borrowers only, term-scoped via klass_student pivot).
     */
    protected function filterByGradeClass(Collection $records, ?int $gradeId, ?int $klassId): Collection {
        if (!$gradeId && !$klassId) {
            return $records;
        }
        $term = \App\Models\Term::currentOrLastActiveTerm();
        $studentQuery = DB::table('klass_student')->where('term_id', $term->id);
        if ($gradeId) {
            $studentQuery->where('grade_id', $gradeId);
        }
        if ($klassId) {
            $studentQuery->where('klass_id', $klassId);
        }
        $studentIds = $studentQuery->pluck('student_id')->toArray();
        return $records->filter(function ($record) use ($studentIds) {
            if ($record->borrower_type !== 'student') {
                return false;
            }
            return in_array($record->borrower_id, $studentIds);
        })->values();
    }

    /**
     * Get circulation report: check-outs and returns for a date range with optional filters.
     */
    public function getCirculationReport(
        Carbon $startDate,
        Carbon $endDate,
        ?string $borrowerType = null,
        ?int $gradeId = null,
        ?int $klassId = null
    ): Collection {
        $query = LibraryTransaction::with(['copy.book.grade', 'borrower'])
            ->whereBetween('checkout_date', [$startDate, $endDate]);

        if ($borrowerType !== null) {
            $query->where('borrower_type', $borrowerType);
        }

        $records = $query->orderBy('checkout_date', 'desc')->get();

        if ($gradeId || $klassId) {
            $records = $this->filterByGradeClass($records, $gradeId, $klassId);
        }

        return $records->map(function ($tx) {
            return [
                'checkout_date' => $tx->checkout_date->format('d M Y'),
                'return_date' => $tx->return_date ? $tx->return_date->format('d M Y') : '-',
                'book_title' => $tx->copy->book->title ?? 'Unknown',
                'accession_number' => $tx->copy->accession_number ?? '-',
                'borrower_name' => $this->getBorrowerName($tx),
                'borrower_type' => $this->settingsKey($tx->borrower_type),
                'status' => ucfirst(str_replace('_', ' ', $tx->status)),
            ];
        })->values();
    }

    /**
     * Get overdue report: current snapshot of all overdue items with fine amounts.
     * No date range filter -- this is a point-in-time view.
     */
    public function getOverdueReport(
        ?string $borrowerType = null,
        ?int $gradeId = null,
        ?int $klassId = null
    ): Collection {
        $query = LibraryTransaction::with(['copy.book', 'borrower', 'fines'])
            ->where('status', 'overdue')
            ->orderBy('due_date', 'asc');

        if ($borrowerType !== null) {
            $query->where('borrower_type', $borrowerType);
        }

        $records = $query->get();

        if ($gradeId || $klassId) {
            $records = $this->filterByGradeClass($records, $gradeId, $klassId);
        }

        return $records->map(function ($tx) {
            return [
                'book_title' => $tx->copy->book->title ?? 'Unknown',
                'accession_number' => $tx->copy->accession_number ?? '-',
                'borrower_name' => $this->getBorrowerName($tx),
                'borrower_type' => $this->settingsKey($tx->borrower_type),
                'checkout_date' => $tx->checkout_date->format('d M Y'),
                'due_date' => $tx->due_date->format('d M Y'),
                'days_overdue' => $tx->due_date->diffInDays(now()),
                'fine_amount' => number_format((float) $tx->fines->where('fine_type', 'overdue')->sum('amount'), 2),
            ];
        })->values();
    }

    /**
     * Get most-borrowed books report ranked by checkout count.
     * Uses DB::table for aggregate GROUP BY query.
     */
    public function getMostBorrowedReport(
        Carbon $startDate,
        Carbon $endDate,
        ?int $gradeId = null,
        int $limit = 50
    ): Collection {
        $query = DB::table('library_transactions')
            ->join('copies', 'library_transactions.copy_id', '=', 'copies.id')
            ->join('books', 'copies.book_id', '=', 'books.id')
            ->leftJoin('grades', 'books.grade_id', '=', 'grades.id')
            ->select(
                'books.id',
                'books.title',
                'books.genre',
                'grades.name as grade_name',
                DB::raw('COUNT(*) as checkout_count'),
                DB::raw('COUNT(DISTINCT CONCAT(library_transactions.borrower_type, "-", library_transactions.borrower_id)) as unique_borrowers')
            )
            ->whereBetween('library_transactions.checkout_date', [$startDate, $endDate]);

        if ($gradeId) {
            $query->where('books.grade_id', $gradeId);
        }

        return $query->groupBy('books.id', 'books.title', 'books.genre', 'grades.name')
            ->orderByDesc('checkout_count')
            ->limit($limit)
            ->get();
    }

    /**
     * Get borrower activity report in aggregate mode: summary stats per borrower.
     */
    public function getBorrowerActivityReport(
        Carbon $startDate,
        Carbon $endDate,
        ?string $borrowerType = null,
        ?int $gradeId = null,
        ?int $klassId = null
    ): Collection {
        $query = LibraryTransaction::with(['borrower'])
            ->whereBetween('checkout_date', [$startDate, $endDate]);

        if ($borrowerType !== null) {
            $query->where('borrower_type', $borrowerType);
        }

        $records = $query->get();

        if ($gradeId || $klassId) {
            $records = $this->filterByGradeClass($records, $gradeId, $klassId);
        }

        // Group by borrower (type + id)
        return $records->groupBy(function ($tx) {
            return $tx->borrower_type . '-' . $tx->borrower_id;
        })->map(function ($transactions, $key) {
            $first = $transactions->first();
            return [
                'borrower_type_raw' => $first->borrower_type,
                'borrower_id' => $first->borrower_id,
                'borrower_name' => $this->getBorrowerName($first),
                'borrower_type' => $this->settingsKey($first->borrower_type),
                'total_checkouts' => $transactions->count(),
                'total_returns' => $transactions->whereNotNull('return_date')->count(),
                'currently_active' => $transactions->whereIn('status', ['checked_out', 'overdue'])->count(),
                'overdue_count' => $transactions->where('status', 'overdue')->count(),
            ];
        })->sortByDesc('total_checkouts')->values();
    }

    /**
     * Get individual borrower report: detailed transaction history for a specific borrower.
     */
    public function getIndividualBorrowerReport(
        string $borrowerType,
        int $borrowerId,
        Carbon $startDate,
        Carbon $endDate
    ): array {
        $transactions = LibraryTransaction::with(['copy.book', 'borrower', 'fines'])
            ->where('borrower_type', $borrowerType)
            ->where('borrower_id', $borrowerId)
            ->whereBetween('checkout_date', [$startDate, $endDate])
            ->orderBy('checkout_date', 'desc')
            ->get();

        $borrower = $transactions->first()?->borrower;
        $borrowerName = $borrower ? ($borrower->full_name ?? $borrower->name ?? '-') : '-';

        $records = $transactions->map(function ($tx) {
            return [
                'checkout_date' => $tx->checkout_date->format('d M Y'),
                'return_date' => $tx->return_date ? $tx->return_date->format('d M Y') : '-',
                'book_title' => $tx->copy->book->title ?? 'Unknown',
                'accession_number' => $tx->copy->accession_number ?? '-',
                'status' => ucfirst(str_replace('_', ' ', $tx->status)),
                'fine_amount' => number_format((float) $tx->fines->sum('amount'), 2),
            ];
        })->values();

        return [
            'borrower_name' => $borrowerName,
            'borrower_type' => $this->settingsKey($borrowerType),
            'records' => $records,
            'summary' => [
                'total_checkouts' => $records->count(),
                'total_returns' => $transactions->whereNotNull('return_date')->count(),
                'active_loans' => $transactions->whereIn('status', ['checked_out', 'overdue'])->count(),
                'total_fines' => number_format((float) $transactions->flatMap->fines->sum('amount'), 2),
            ],
        ];
    }

    /**
     * Get collection development report: books grouped by category and grade with utilization rates.
     * Uses DB::table for aggregate GROUP BY query.
     */
    public function getCollectionDevelopmentReport(?int $gradeId = null): Collection {
        $query = DB::table('books')
            ->leftJoin('copies', 'books.id', '=', 'copies.book_id')
            ->leftJoin('grades', 'books.grade_id', '=', 'grades.id')
            ->select(
                'books.genre',
                'grades.name as grade_name',
                DB::raw('COUNT(DISTINCT books.id) as total_titles'),
                DB::raw('COUNT(copies.id) as total_copies'),
                DB::raw("SUM(CASE WHEN copies.status = 'available' THEN 1 ELSE 0 END) as available_copies"),
                DB::raw("SUM(CASE WHEN copies.status = 'checked_out' THEN 1 ELSE 0 END) as checked_out_copies"),
                DB::raw("SUM(CASE WHEN copies.status = 'lost' THEN 1 ELSE 0 END) as lost_copies")
            );

        if ($gradeId) {
            $query->where('books.grade_id', $gradeId);
        }

        $results = $query->groupBy('books.genre', 'grades.name')
            ->orderBy('books.genre')
            ->orderBy('grades.name')
            ->get();

        return $results->map(function ($row) {
            $totalCopies = $row->total_copies ?: 1;
            $row->utilization_rate = round(($row->checked_out_copies / $totalCopies) * 100, 1);
            return $row;
        });
    }

    /**
     * Get fine collection report: fines assessed, collected, waived, and outstanding for a date range.
     * ALL monetary aggregation uses bcmath -- NEVER float arithmetic on money.
     */
    public function getFineCollectionReport(
        Carbon $startDate,
        Carbon $endDate,
        ?string $borrowerType = null
    ): array {
        $query = LibraryFine::with(['transaction.copy.book', 'borrower'])
            ->whereBetween('fine_date', [$startDate, $endDate]);

        if ($borrowerType !== null) {
            $query->where('borrower_type', $borrowerType);
        }

        $fines = $query->orderBy('fine_date', 'desc')->get();

        // Aggregate with bcmath -- NEVER use float arithmetic on money
        $totalAssessed = '0.00';
        $totalCollected = '0.00';
        $totalWaived = '0.00';
        $totalOutstanding = '0.00';

        $records = $fines->map(function ($fine) use (&$totalAssessed, &$totalCollected, &$totalWaived, &$totalOutstanding) {
            $totalAssessed = bcadd($totalAssessed, (string) $fine->amount, 2);
            $totalCollected = bcadd($totalCollected, (string) $fine->amount_paid, 2);
            $totalWaived = bcadd($totalWaived, (string) $fine->amount_waived, 2);
            $outstanding = bcsub(
                (string) $fine->amount,
                bcadd((string) $fine->amount_paid, (string) $fine->amount_waived, 2),
                2
            );
            $totalOutstanding = bcadd($totalOutstanding, $outstanding, 2);

            return [
                'fine_date' => $fine->fine_date->format('d M Y'),
                'book_title' => $fine->transaction->copy->book->title ?? 'Unknown',
                'borrower_name' => $this->getBorrowerName($fine),
                'borrower_type' => $this->settingsKey($fine->borrower_type),
                'fine_type' => ucfirst(str_replace('_', ' ', $fine->fine_type)),
                'amount' => number_format((float) $fine->amount, 2),
                'amount_paid' => number_format((float) $fine->amount_paid, 2),
                'amount_waived' => number_format((float) $fine->amount_waived, 2),
                'outstanding' => number_format((float) $outstanding, 2),
                'status' => ucfirst($fine->status),
            ];
        })->values();

        return [
            'records' => $records,
            'summary' => [
                'total_assessed' => $totalAssessed,
                'total_collected' => $totalCollected,
                'total_waived' => $totalWaived,
                'total_outstanding' => $totalOutstanding,
                'fine_count' => $fines->count(),
            ],
        ];
    }
}
