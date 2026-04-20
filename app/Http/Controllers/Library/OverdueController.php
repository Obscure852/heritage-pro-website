<?php

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use App\Models\Library\LibraryTransaction;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class OverdueController extends Controller {
    /**
     * Display the overdue items dashboard grouped by aging brackets.
     */
    public function index(): View {
        Gate::authorize('manage-library');

        $overdueItems = LibraryTransaction::with(['copy.book', 'borrower', 'checkedOutBy'])
            ->where('status', 'overdue')
            ->orderBy('due_date', 'asc')
            ->get()
            ->map(function ($transaction) {
                $transaction->days_overdue = $transaction->due_date->diffInDays(now());
                return $transaction;
            });

        // Group into aging brackets
        $brackets = [
            '1-7 days' => $overdueItems->filter(fn($t) => $t->days_overdue >= 1 && $t->days_overdue <= 7)->values(),
            '8-14 days' => $overdueItems->filter(fn($t) => $t->days_overdue >= 8 && $t->days_overdue <= 14)->values(),
            '15-30 days' => $overdueItems->filter(fn($t) => $t->days_overdue >= 15 && $t->days_overdue <= 30)->values(),
            '30+ days' => $overdueItems->filter(fn($t) => $t->days_overdue > 30)->values(),
        ];

        // Summary stats
        $totalOverdue = $overdueItems->count();
        $stats = [];
        foreach ($brackets as $label => $items) {
            $stats[$label] = $items->count();
        }

        return view('library.overdue.dashboard', compact('brackets', 'totalOverdue', 'stats'));
    }
}
