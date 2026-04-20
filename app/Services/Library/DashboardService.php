<?php

namespace App\Services\Library;

use App\Models\Book;
use App\Models\Copy;
use App\Models\Library\LibraryAuditLog;
use App\Models\Library\LibraryTransaction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardService {
    const ACTION_LABELS = [
        'checkout' => 'Book checked out',
        'checkin' => 'Book returned',
        'renewal' => 'Loan renewed',
        'fine_assessed' => 'Fine assessed',
        'fine_payment' => 'Fine payment recorded',
        'fine_waiver' => 'Fine waived',
        'reservation_placed' => 'Reservation placed',
        'reservation_fulfilled' => 'Reservation fulfilled',
        'lost_fine_assessed' => 'Lost book fine assessed',
    ];

    /**
     * Get today's statistics: checkouts, returns, new registrations.
     *
     * "New registrations" = unique borrowers whose very first library checkout happened today.
     *
     * @return array{checkouts: int, returns: int, newRegistrations: int}
     */
    public function todayStats(): array {
        $checkouts = LibraryTransaction::whereDate('checkout_date', today())->count();
        $returns = LibraryTransaction::whereDate('return_date', today())->count();

        // New registrations: first-time borrowers today (no prior checkout exists)
        $newRegistrations = DB::table('library_transactions as t')
            ->whereDate('t.checkout_date', today())
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('library_transactions as prev')
                    ->whereColumn('prev.borrower_type', 't.borrower_type')
                    ->whereColumn('prev.borrower_id', 't.borrower_id')
                    ->whereColumn('prev.checkout_date', '<', 't.checkout_date');
            })
            ->distinct()
            ->count(DB::raw('CONCAT(t.borrower_type, "-", t.borrower_id)'));

        return compact('checkouts', 'returns', 'newRegistrations');
    }

    /**
     * Get all transactions due today (checked_out or overdue status).
     *
     * Eager-loads copy.book and borrower to avoid N+1 queries.
     *
     * @return Collection
     */
    public function dueToday(): Collection {
        return LibraryTransaction::with(['copy.book', 'borrower'])
            ->whereIn('status', ['checked_out', 'overdue'])
            ->whereDate('due_date', today())
            ->orderBy('due_date')
            ->get();
    }

    /**
     * Get collection health summary: total books, total copies, and copy status breakdown.
     *
     * @return array{total_books: int, total_copies: int, available: int, checked_out: int, lost: int, on_hold: int}
     */
    public function collectionSummary(): array {
        $copyStatusCounts = Copy::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'total_books' => Book::count(),
            'total_copies' => $copyStatusCounts->sum(),
            'available' => $copyStatusCounts->get('available', 0),
            'checked_out' => $copyStatusCounts->get('checked_out', 0),
            'lost' => $copyStatusCounts->get('lost', 0),
            'on_hold' => $copyStatusCounts->get('on_hold', 0),
            'missing' => $copyStatusCounts->get('missing', 0),
        ];
    }

    /**
     * Get overdue items grouped by aging brackets with summary counts.
     *
     * Reuses exact bracket logic from OverdueController (1-7, 8-14, 15-30, 30+).
     *
     * @return array{summary: array, total: int}
     */
    public function overdueByBracket(): array {
        $overdueItems = LibraryTransaction::with(['copy.book', 'borrower'])
            ->where('status', 'overdue')
            ->orderBy('due_date', 'asc')
            ->get()
            ->map(function ($transaction) {
                $transaction->days_overdue = $transaction->due_date->diffInDays(now());
                return $transaction;
            });

        $brackets = [
            '1-7 days' => $overdueItems->filter(fn($t) => $t->days_overdue >= 1 && $t->days_overdue <= 7)->values(),
            '8-14 days' => $overdueItems->filter(fn($t) => $t->days_overdue >= 8 && $t->days_overdue <= 14)->values(),
            '15-30 days' => $overdueItems->filter(fn($t) => $t->days_overdue >= 15 && $t->days_overdue <= 30)->values(),
            '30+ days' => $overdueItems->filter(fn($t) => $t->days_overdue > 30)->values(),
        ];

        $summary = collect($brackets)->map->count()->toArray();
        $total = $overdueItems->count();

        return compact('summary', 'total');
    }

    /**
     * Get the most popular books for the current term by checkout count.
     *
     * @param int $limit Number of books to return (default 10)
     * @return Collection
     */
    public function popularBooks(int $limit = 10): Collection {
        $term = \App\Models\Term::currentOrLastActiveTerm();

        return DB::table('library_transactions')
            ->join('copies', 'library_transactions.copy_id', '=', 'copies.id')
            ->join('books', 'copies.book_id', '=', 'books.id')
            ->select('books.id', 'books.title', DB::raw('COUNT(*) as checkout_count'))
            ->when($term, function ($q) use ($term) {
                $q->whereBetween('library_transactions.checkout_date', [
                    $term->start_date, $term->end_date,
                ]);
            })
            ->groupBy('books.id', 'books.title')
            ->orderByDesc('checkout_count')
            ->limit($limit)
            ->get();
    }

    /**
     * Get recent library activity from audit logs.
     *
     * @param int $limit Number of entries to return (default 20)
     * @return Collection
     */
    public function recentActivity(int $limit = 20): Collection {
        return LibraryAuditLog::with('user')
            ->whereIn('action', array_keys(self::ACTION_LABELS))
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get a human-readable label for an audit action.
     *
     * @param string $action Raw action key
     * @return string
     */
    public static function actionLabel(string $action): string {
        return self::ACTION_LABELS[$action] ?? ucfirst(str_replace('_', ' ', $action));
    }
}
