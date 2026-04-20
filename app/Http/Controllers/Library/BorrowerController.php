<?php

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use App\Models\Library\LibraryFine;
use App\Models\Library\LibraryTransaction;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BorrowerController extends Controller {
    /**
     * Display borrower search page.
     */
    public function index(): View {
        return view('library.borrowers.index');
    }

    /**
     * AJAX search for borrowers (students and staff).
     */
    public function search(Request $request): JsonResponse {
        $request->validate([
            'search' => ['required', 'string', 'min:2'],
        ]);

        $search = $request->search;

        // Search students
        $students = Student::with(['currentGrade'])
            ->where('status', Student::STATUS_CURRENT)
            ->where(function ($query) use ($search) {
                $query->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('id_number', 'like', "%{$search}%")
                    ->orWhere('exam_number', 'like', "%{$search}%");
            })
            ->limit(10)
            ->get()
            ->map(function ($student) {
                return [
                    'id' => $student->id,
                    'type' => 'student',
                    'borrower_type' => 'student',
                    'name' => $student->full_name,
                    'identifier' => $student->exam_number ?? $student->id_number ?? $student->id,
                    'extra' => optional($student->currentGrade)->name ?? 'N/A',
                ];
            });

        // Search staff
        $staff = User::where('status', 'Current')
            ->where(function ($query) use ($search) {
                $query->where('firstname', 'like', "%{$search}%")
                    ->orWhere('lastname', 'like', "%{$search}%")
                    ->orWhere('id_number', 'like', "%{$search}%");
            })
            ->limit(10)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'type' => 'staff',
                    'borrower_type' => 'user',
                    'name' => $user->full_name,
                    'identifier' => $user->id_number ?? $user->id,
                    'extra' => $user->position ?? $user->department ?? 'Staff',
                ];
            });

        return response()->json($students->merge($staff)->values());
    }

    /**
     * Display borrower profile with current loans, history, and fines.
     */
    public function show(Request $request, string $type, int $id): View {
        $borrowerClass = $type === 'student' ? Student::class : User::class;
        $borrower = $borrowerClass::findOrFail($id);
        $morphType = $type === 'student' ? 'student' : 'user';

        // Current active loans
        $currentLoans = LibraryTransaction::with(['copy.book'])
            ->forBorrower($morphType, $id)
            ->active()
            ->orderBy('due_date')
            ->get();

        // Borrowing history with date range filtering
        $historyQuery = LibraryTransaction::with(['copy.book'])
            ->forBorrower($morphType, $id)
            ->where('status', 'returned');

        if ($request->filled('from')) {
            $historyQuery->where('checkout_date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $historyQuery->where('checkout_date', '<=', $request->to);
        }

        $history = $historyQuery->orderByDesc('checkout_date')->paginate(20);

        // Outstanding fines
        $outstandingFines = LibraryFine::with(['transaction.copy.book'])
            ->forBorrower($morphType, $id)
            ->unpaid()
            ->get();

        $totalFinesOutstanding = $outstandingFines->sum('outstanding');

        return view('library.borrowers.show', [
            'borrower' => $borrower,
            'type' => $type,
            'currentLoans' => $currentLoans,
            'history' => $history,
            'outstandingFines' => $outstandingFines,
            'totalFinesOutstanding' => $totalFinesOutstanding,
        ]);
    }
}
